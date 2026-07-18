# Known Attributes & Capture History — Design

Follow-on to milestone 5 (attribute routing). Today an admin building a routing
rule must hand-type the exact SAML attribute name — full claim URIs for Entra,
easy to typo, with nothing tying the field to what the customer's IdP actually
sends. This feature captures the attribute *names* an IdP asserts on each login
and offers them as a strict dropdown in the rule editor, backed by a names-only
capture-history log for provenance.

Two facts shaped the design:

- **IdP metadata does not carry the attributes.** Okta and Entra configure
  claims per-application; those names are not published in the
  `<IDPSSODescriptor>` we already parse. The only reliable source is a real
  assertion's `<AttributeStatement>`, which the ACS already has in hand via
  `$auth->getAttributes()`.
- **Assertion *values* are PHI/PII; attribute *names* are not.** This feature
  persists names only. Value-level assertion logging (forensics grade) is a
  separate, later, compliance-scoped spec and is explicitly out of scope here.

## Scope decisions (settled during design)

- Source: auto-capture from every login, plus manual add/remove.
- Policy: merge-and-grow, never auto-shrink; capture on all non-admin-portal
  clients.
- Identity attributes (the three `attribute_map` values — email/first/last) are
  excluded from capture: they are already handled by the fixed identity map and
  would be noise in a routing dropdown. `attribute_map` is the exclusion filter,
  not the storage.
- The rule editor's attribute field is a **strict dropdown** over the client's
  known set; the manual-add path is what unblocks building rules before the
  first login (or for a claim the IdP will send but hasn't yet).

## Schema

Two additions, both additive/reversible (auto-run via `migrations_login`):

`saml_clients.known_attributes` — json, default `[]`. The fast-read set the
rule-editor dropdown consumes; a derived cache over the observation log below.
Mirrors the existing `email_domains` json column exactly (fillable, `array`
cast).

`saml_attribute_observations` — the names-only capture history (provenance /
backup for `known_attributes`):

| column          | type            | notes                                         |
|-----------------|-----------------|-----------------------------------------------|
| id              | bigint pk       |                                               |
| saml_client_id  | unsigned bigint | observations die with the client              |
| name            | string          | the asserted attribute name (NEVER a value)   |
| first_seen_at   | timestamp       | set once, on first observation                |
| last_seen_at    | timestamp       | bumped every login the name appears           |
| observation_count | unsigned int  | incremented every login the name appears      |
| timestamps      |                 |                                               |

Unique index `(saml_client_id, name)`. App-owned, stock conventions.
`SamlClient` gains `attributeObservations(): HasMany`.

## Capture (login hot path)

`App\Saml\KnownAttributeCollector::capture(SamlClient $client, array $attributes): void`

Called from `SamlController::acs` after identity extraction, **before** the
admin-portal branch and the provisioner (admin-portal clients are skipped
entirely — they assert Employee identities, not routing attributes).

- Compute the candidate names: `array_keys($attributes)` minus the client's
  `attribute_map` values (the identity attributes). Never touches values.
- **Observation upsert (always):** for each candidate name, one
  `updateOrInsert` on `(saml_client_id, name)` — set `first_seen_at` on insert,
  bump `last_seen_at` + `observation_count` on update. This is the running
  audit; it records every appearance regardless of whether the name is new.
- **known_attributes merge (only when new):** if any candidate name is absent
  from `$client->known_attributes`, union them in and persist the column once.
  In steady state (no new names) this branch does zero writes to `saml_clients`.
- The whole method is wrapped so any failure is logged
  (`Log::warning('known-attribute capture failed', ...)`) and swallowed — a
  capture problem must never break a login. It runs after the assertion is
  already validated, so it never gates authentication.

Cost per login: N small indexed upserts (N = distinct non-identity attributes,
typically 1–5) plus at most one `saml_clients` update when the IdP adds a claim.
Acceptable on the hot path; the upserts are the price of the audit trail the
feature is meant to provide.

## Manual management (manager + API)

- `SamlClientManager` validates `known_attributes` (`sometimes`, array, each
  entry a non-empty string; trimmed + de-duplicated) and adds it to
  `EDITABLE_FIELDS`, so an admin adds/removes names through the existing update
  path (and audit trail).
- Removing a name a live rule still references is allowed — the rule matches
  against the incoming assertion, not the known list, so it keeps working; the
  portal warns rather than blocks. The name's observation row is left intact
  (provenance survives a prune, and the next login re-adds it to
  `known_attributes` if the IdP still sends it).
- The client detail payload (`SamlClientController::detail`) returns
  `known_attributes` and, for each, its `last_seen_at` from the observation log
  (so the portal can distinguish live claims from stale ones). List payloads are
  unchanged.

## Rule editor (portal)

- In both org-rule and department-rule rows, the attribute `el-input` becomes a
  strict `el-select` populated from `detail.known_attributes`. Catch-all rows
  still bypass the attribute field entirely (unchanged).
- Empty-set affordance: when `known_attributes` is empty the select shows a
  disabled hint ("No attributes captured yet — add one below, or complete a
  test login"), so the editor is never a dead end.
- A "Known attributes" strip in the routing section: a tag input (same idiom as
  Email domains) to add a name, and removable tags to prune, each showing its
  `last_seen_at` as a muted note ("seen 2d ago" / "never"). Saving posts through
  the client update endpoint. Bundle rebuilt via the node:14 container, dist
  committed.

## Testing

**Fixtures — use the fluent hierarchy factories, not the old helper.** Since
this spec was written the factories gained a hierarchy builder; tests here build
System → Organization → Department trees with it rather than hand-assembling
`SystemOrganizations` rows or the `SeedsSystemHierarchy` trait:

- `System::factory()->withOrganizations($orgCount, $deptsEach)` — whole tree in
  one call (orgs attached via `SystemOrganizations`, each with departments).
- `Organization::factory()->forSystem($system|$name|null)->withDepartments(int|array)`
  — one org into a system (string looks up/creates a shared system; the join
  row is keyed on the org, enforcing one-system-per-org), with a department
  count or exact names.
- `DepartmentFactory::NAMES` is the healthcare name pool; `withDepartments(int)`
  sequences distinct names so per-org uniqueness holds without exhausting a
  global `unique()`.

Prefer these in new tests. The two existing routing tests that still use the
`SeedsSystemHierarchy` trait (`AttributeRouterTest`, `RoutingRuleManagerTest`)
may be migrated onto `withOrganizations()` opportunistically when this work
touches them, but that migration is not required by this spec.

- **Unit/feature (collector):** unions asserted names into `known_attributes`;
  excludes `attribute_map` values; writes `saml_clients` only when a name is
  new (assert no update when nothing new); upserts an observation row per
  candidate every login (first_seen set once, last_seen + count bump on repeat);
  never persists a value anywhere; no-ops for admin-portal clients; a thrown
  error inside capture does not break the ACS login (the login still succeeds).
- **Feature (manager/API):** `known_attributes` validation (non-string,
  duplicates, trimming); add/remove round trip; detail payload includes the set
  with `last_seen_at`.
- **E2E (Dusk + mock IdP):** a real `local-idp` login populates the set from the
  mock assertion (`uid`, `eduPersonAffiliation`); the rule editor's attribute
  dropdown then offers a captured name; a manual add on a fresh client unblocks
  the dropdown before any login.

  Cross-org capture/routing fixtures that need a system with several orgs use
  `System::factory()->withOrganizations(...)` per the fixture note above.

## Rollout

Additive and inert: existing clients start with `known_attributes = []` and an
empty observation log, self-populating on the next login; the strict dropdown
degrades gracefully to "add manually" until then. Stacks on `milestone5-routing`
(login) and its portal twin, same PR. No behavior change to any existing login
path beyond the swallowed, post-validation capture call.
