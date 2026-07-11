# Attribute-Based User Routing — Design

Milestone 5 of the SSO contract: route SAML users to the right place in the
Apex hierarchy (System → Organization → Department) based on what the
customer's IdP asserts — department, role, group, or any other attribute —
instead of the single static default department each client carries today.

Builds directly on the polymorphic client ownership refactor
(`docs/specs/2026-07-10-client-ownership.md`). Routing is **two separate
ordered rule lists**, because the two questions are different and repeat
differently:

- **Organization rules** answer "which organization does this user belong
  to?" — only meaningful on system-owned clients (an org-owned client's
  organization *is* its owner; stage 1 is skipped).
- **Department rules** answer "which department within that organization?" —
  and target a **department name**, not a `Departments.ID`. A hospital
  system with a dozen identically-structured orgs writes its department
  mappings once; the name resolves against whatever organization stage 1
  picked. No nullable columns anywhere: department rules simply have no
  organization column.

Rules read like Cloudflare page rules: "if the assertion matches [attribute]
[operator] [value], then …".

Scope decisions (settled during design):

- **Rules decide placement only.** Credential/professional role stays with
  the finish-account flow; rules never gate login (authorization stays
  membership-based).
- **Operators** (a closed set, shared by both rule kinds): `wildcard`,
  `strict_wildcard`, `equals`, `not_equals`, `starts_with`,
  `not_starts_with`, `contains`, `not_contains`, `ends_with`,
  `not_ends_with`. Wildcards are `*`-patterns (zero or more characters,
  anchored over the whole value); `wildcard` is case-insensitive,
  `strict_wildcard` case-sensitive (Cloudflare's exact distinction). Every
  other operator compares case-insensitively (deliberate deviation from
  Cloudflare's case-sensitive `equals`: hospital IdPs emit inconsistent
  casing).
- **Operators are a native PHP backed enum, not loose strings.**
  `App\Saml\RoutingOperator: string` (cases `Wildcard = 'wildcard'`,
  `StrictWildcard = 'strict_wildcard'`, `Equals = 'equals'`, … all ten) is
  the single source of truth: models cast to it, validators use
  `Rule::enum(RoutingOperator::class)`, the router `match`es on enum cases
  (exhaustive — a new case fails loudly until handled), the API serializes
  `->value`, and the portal dropdown is generated from the same list.
  Nothing compares raw operator strings anywhere.
- **First match wins within each list — with resolution fall-through for
  department rules.** A department rule only wins if it matches AND its
  named department exists (active) in the resolved organization; otherwise
  evaluation falls through to the next rule. Shared rule sets stay robust
  when only 8 of 12 orgs have a Cath Lab.
- **The IdP is authoritative, evaluated on every login** — for department
  moves. A department rule that resolves to a department different from the
  user's current one moves them (including across orgs when stage 1 says
  so). No resolvable match changes nothing: routing never demotes or
  unplaces anyone. Apex admins' manual placements survive only until the
  IdP asserts something a department rule matches; the runbook says so
  explicitly.
- **The catch-all is spelled in the grammar — no nulls.** The reserved
  triple `attribute = *`, `operator = wildcard`, `value = *` matches every
  login (attribute `*` is a sentinel, only valid in exactly this
  combination). Legal only as the **last** rule of its list; rules after a
  catch-all are unreachable and rejected. An org-rule catch-all is how a
  system client says "everyone else belongs to org X"; a department-rule
  catch-all is "everyone else lands in the department named Y (where it
  exists)".

## Schema

Two app-owned tables, stock Laravel conventions, additive migration (runs via
the `migrations_login` startup migrate):

`saml_org_rules` — system-owned clients only:

| column          | type            | notes                                              |
|-----------------|-----------------|----------------------------------------------------|
| id              | bigint pk       |                                                    |
| saml_client_id  | unsigned bigint | rules die with the client                          |
| position        | unsigned int    | evaluation order; unique per client                |
| attribute       | string          | assertion attribute name, verbatim as the IdP emits it (full claim URI for Entra); `*` reserved for the catch-all |
| operator        | string          | `RoutingOperator` value; model casts to the enum   |
| value           | string          | match value / `*`-pattern                          |
| organization_id | int             | legacy `Organizations.ID`; must be in the owner's scope |
| timestamps      |                 |                                                    |

`saml_department_rules` — both ownership kinds:

| column          | type            | notes                                              |
|-----------------|-----------------|----------------------------------------------------|
| id              | bigint pk       |                                                    |
| saml_client_id  | unsigned bigint | rules die with the client                          |
| position        | unsigned int    | evaluation order; unique per client                |
| attribute       | string          | as above; `*` reserved                             |
| operator        | string          | `RoutingOperator` value                            |
| value           | string          | match value / `*`-pattern                          |
| department_name | string          | matched case-insensitively against the resolved org's active departments' `Name` |
| timestamps      |                 |                                                    |

`SamlClient` gains ordered `orgRules()` / `departmentRules()` hasMany
relations; `SamlOrgRule` / `SamlDepartmentRule` models (each with an
`isCatchAll(): bool` helper) + factories follow app-owned conventions.

## The router

`App\Saml\AttributeRouter::route(SamlClient $client, array $attributes, ?int $fallbackOrganizationId = null): ?array`

Returns `['organization_id' => int, 'department_id' => ?int]`, or `null`
when no organization can be determined (system-owned, no org rule matched).

- **Stage 1 — organization.** Org-owned: the owner org, always (org rules
  are invalid on such clients and ignored defensively). System-owned: walk
  `orgRules` in position order, first match wins; no match → `null` (the
  caller decides what that means for new vs existing users).
- **Stage 2 — department.** Fetch the resolved org's active department
  names once (`Departments` where `OrganizationID`, `Active = 'Y'`). Walk
  `departmentRules` in position order; the first rule that **matches the
  assertion AND whose `department_name` resolves** (case-insensitive) wins →
  that department's ID. None resolve → `department_id = null` (finish-account
  flow in the resolved org).
- SAML attributes are multi-valued. **Positive operators** match when any
  asserted value satisfies the comparison; an absent attribute never
  matches. **Negated operators** (`not_*`) match when **no** asserted value
  satisfies the positive form — vacuously including an absent attribute.
- Wildcards: `preg_quote`, then `\*` → `.*`, wrapped `/^…$/u` (+`i` for
  `wildcard`). The catch-all triple always matches.
- One `Departments` query per login on the resolved org; otherwise a pure
  function over its inputs — no user lookups, no session, no logging.

## Login flow integration

`SamlController::acs` orchestrates: it already holds `$auth->getAttributes()`;
it calls the router once and hands the result to the provisioner and the
session (admin-portal clients branch away earlier and are untouched):

- `SamlUserProvisioner::provision(SamlClient $client, string $email,
  ?string $firstName, ?string $lastName, ?array $placement): User`
  - **JIT creation:** placement with department → that `DepartmentID`;
    placement without department → `DepartmentID = 0` (finish-account in the
    placed org); `null` placement on an **org-owned** client → impossible
    (stage 1 always yields the owner; JIT default `$client->department_id ??
    0` applies when no department rule resolves — via a department-rule-less
    list this degrades to exactly today's behavior); `null` placement on a
    **system-owned** client → the ownership spec's `unrouted_user` rejection
    (an org-rule catch-all is how to accept everyone).
  - **Existing users:** a placement whose department differs from the user's
    current `DepartmentID` moves them and logs (`SAML routed user to
    department`, client slug, user id, from, to). Department-less and `null`
    placements leave the user untouched. For system-owned existing users
    with no org-rule match, stage 2 resolves against the user's current
    department's org (their placement can still be corrected within their
    org). Name sync unchanged.
- `establishSession` puts `Organization` = placed org, falling back to the
  ownership spec's behavior (org-owned → owner org; system-owned → the
  user's department's org). The session `Organization` reflects a placement
  only when it was applied (a resolved department or a JIT creation); an
  unmoved existing user keeps their department's org.

**Precedence note (org-owned):** when department rules exist and one
resolves, it beats the client's static default department; the default is
the no-rule/no-match fallback only.

## Validation

`SamlClientManager::replaceRoutingRules(SamlClient $client, array $orgRules, array $departmentRules): SamlClient`

- Replace-both-lists semantics in one transaction (grants precedent):
  validates every rule before touching rows; positions assigned from array
  order.
- Org rules: only accepted for system-owned clients (org-owned with a
  non-empty org-rule list → ValidationException); `organization_id` must be
  in `scopedOrganizationIds()`.
- Both lists: `attribute`, `operator`, `value` (and `department_name` /
  `organization_id` respectively) required non-empty; `operator` via
  `Rule::enum(RoutingOperator::class)`; attribute `*` only in the exact
  catch-all triple; catch-all only in the final position of its list.
- `department_name` is deliberately NOT validated against any org's
  departments (it is a cross-org template; the portal offers suggestions,
  the resolver handles absence by design).
- Re-parenting a client requires clearing its routing rules first.

## Admin surfaces

- **API** (admin.api group, audited):
  - `GET /api/admin/saml-clients/{slug}/routing-rules` →
    `{org_rules: […], department_rules: […]}`, each ordered, org rules with
    `organization_name` alongside the ID, plus derived `catch_all` flags.
  - `PUT /api/admin/saml-clients/{slug}/routing-rules` → replaces both lists
    atomically; body `{org_rules: [{attribute, operator, value,
    organization_id}], department_rules: [{attribute, operator, value,
    department_name}]}`. Audit context logs both counts and the rule tuples.
  - `GET /api/admin/saml-clients/{slug}/routable-organizations` → the owner
    scope's organizations (portal picker for org-rule targets).
- **CLI:** `saml:client routing {slug}` renders both lists as sentences
  (`org 1. hospital equals "Mercy West" → Mercy West`, `dept 2. department
  contains "ICU" → "ICU Nursing"`). Replacement takes JSON — the same object
  the PUT endpoint accepts — via `--set '<json>'` or `--set-file=<path>`;
  `--clear` empties both lists. `describe` shows rule counts. (No wizard
  mode — the portal is the friendly editor.)
- **Portal** (`website_admin`, SSO Clients island): a "Routing" panel in the
  edit dialog with two sections. Organization rules (system-owned clients
  only): attribute / operator dropdown / value → org dropdown
  (routable-organizations). Department rules: attribute / operator dropdown /
  value → department-name input with suggestions (union of department names
  across the owner scope, via the existing departments lookup per org).
  Operator dropdown labels: "equals", "does not equal", "wildcard
  (case-insensitive)", "strict wildcard (case-sensitive)", …. A "match
  everyone" toggle per row fills the reserved `*` / `wildcard` / `*` triple
  and disables the match inputs (last row of its list only). Add / remove /
  reorder per list, saved via the bridge to the PUT endpoint. Bundle rebuilt
  via the node:14 container, dist committed.

## Testing

- **Unit (router):** stage-1 org resolution (org-owned always owner;
  system-owned first-match; no-match null); every operator positive and
  negative, wildcard vs strict_wildcard case behavior, anchored patterns;
  negated operators against multi-valued attributes and absent attributes
  (vacuous match); positive operators never match absent attributes;
  stage-2 fall-through (matching rule whose name is missing in the resolved
  org yields to a later resolvable rule); department-name case-insensitive
  resolution against active departments only; catch-alls in both lists;
  empty lists.
- **Feature:** system-owned JIT routed cross-org via the seeded local
  system; org-rule catch-all rescues an otherwise-unrouted system JIT;
  unrouted system JIT without catch-all rejected (`unrouted_user`);
  org-owned JIT with a resolving department rule beats the static default;
  org-owned unrouted keeps today's default/finish behavior; existing user
  moved on resolving department rule (same-org and cross-org); existing
  user untouched when nothing resolves (never demoted); system-owned
  existing user with no org match gets own-org department correction and
  correct session org; disabled-user and JIT-off paths unaffected; manager
  validation (org rules on an org-owned client, org outside owner scope,
  unknown operator, `*` outside the exact triple, rules after a catch-all,
  empty fields) rejects atomically; API GET/PUT round trip + audit +
  routable-organizations; CLI list, `--set` inline, `--set-file`, `--clear`,
  malformed JSON.
- **E2E (Dusk + mock IdP):** a department rule on `local-idp` keyed on the
  mock IdP's static `eduPersonAffiliation` (`group1` for user1) targeting
  the seeded "SSO Department" by name → fresh JIT login lands on the finish
  screen (credential still unset) with `DepartmentID` already routed, not 0.
  Portal Dusk test drives both sections of the rules panel
  (add an org rule + a department rule via the pickers, save, reload,
  assert persisted).
- **Docs:** onboarding runbook section — the two rule kinds and why
  (write-once department templates), ordering + fall-through semantics, the
  ten operators (negation-vs-multi-valued, wildcard case rules), catch-alls,
  owner-scope confinement, the IdP-authoritative caveat (manual placements
  are overridden on the next login a department rule resolves), and
  Entra/Okta attribute-name reminders.

## Rollout

Branch `milestone5-routing` stacked on `client-ownership` (login) and a
stacked `website_admin` branch for the portal panel — same PR pattern as
milestones 3/4/bonus. Deploy is inert: no rules exist until an admin creates
them; org-owned behavior without rules is byte-for-byte today's.
