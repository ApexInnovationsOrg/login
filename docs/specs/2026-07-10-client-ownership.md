# Polymorphic Client Ownership — Design

Prerequisite refactor for milestone 5 (attribute routing). The Apex hierarchy
is **System → Organization → Department**, and a hospital system commonly runs
one IdP and one email domain across all of its organizations. Today a SAML
client is anchored to exactly one organization (`saml_clients.organization_id`),
which makes a system-wide client impossible without crowning an arbitrary
"home org". This refactor lets a client be owned at **either level**:

- `owner_type = 'organization'` — exactly today's semantics, unchanged
  behavior everywhere.
- `owner_type = 'system'` — the client spans the system's organizations;
  placement of new users comes from routing rules (the milestone 5 spec,
  stacked on this one). Until rules exist, a system-owned client serves
  existing users only.

Nothing in milestones 2–4 has merged or deployed, so this lands as ordinary
migrations on top of the stack — the reviewed PRs stay untouched and the
shared production database sees one coherent migration sequence at first
deploy.

## Schema

One migration, two tables, both reversible:

- `saml_clients`: add `owner_type` (string) + `owner_id` (unsigned int),
  backfill `('organization', organization_id)` for every row, then drop
  `organization_id`.
- `sso_grants`: the same — add owner pair, backfill from `organization_id`,
  drop it; unique key becomes `(user_id, owner_type, owner_id)`. Grants are
  **owner-scoped**: an org-owned client's manager list is exactly today's
  org list; a system-owned client has one system-level manager list.

`SamlClient` gains the scope helpers everything else consumes:

- `ownedByOrganization(): bool`
- `ownerName(): ?string` — Organizations.Name or Systems.Name
- `scopedOrganizationIds(): array` — org-owned → `[owner_id]`; system-owned →
  the system's organization IDs via `SystemOrganizations` (an organization
  belongs to exactly one system by business rule; the join table doesn't
  enforce it, so the query simply selects by `SystemID = owner_id`).

## Validation (SamlClientManager)

- Create requires exactly one owner: `owner_type` ∈ {organization, system} +
  `owner_id` that exists in the corresponding legacy table. Update may
  re-parent with the same validation.
- `department_id` (the JIT default) is only legal on org-owned clients and
  must belong to the owning organization — this also closes the milestone-4
  backlog gap (department existence/ownership was never validated). A
  system-owned client's `department_id` must be null; re-parenting to system
  ownership with a default department set is rejected until it's cleared.
- Grants replacement validates each granted user's department belongs to an
  organization in `scopedOrganizationIds()`.

## Login flow

- **Existing users:** unchanged for org-owned. For system-owned, the session
  `Organization` key comes from the user's department's organization (the
  client has no single org to offer).
- **New JIT users on system-owned clients are rejected** (standard rejection
  page, logged `reason: unrouted_user`): with no home org there is nowhere to
  aim the finish-account flow. The routing spec relaxes exactly this — rules
  (including an explicit catch-all) become the placement mechanism.
- Org-owned JIT, name sync, disabled checks, domains, the admin-portal
  (bonus) branch: all byte-for-byte unchanged.

## Admin surfaces

- **API:** `item()`/`detail()` replace `organization_id`/`organization_name`
  with `owner: {type, id, name}`; `grants_count` counts the owner's list.
  Create/update accept `owner_type` + `owner_id`. The grants endpoints keep
  their shapes, scoped by owner. The user-search lookup (grants picker)
  searches across `scopedOrganizationIds()`.
- **CLI:** `saml:client create` takes `--org=<id>` XOR `--system=<id>`
  (replacing the required `--org`); `update` accepts either to re-parent.
  `list` shows an Owner column (`org #5` / `system #2`); `describe` shows
  `Owner: organization 5 (Name)` style. The wizard asks owner type first,
  then searches the matching table.
- **Portal (SSO Clients island):** create/edit dialogs get an owner-type
  radio (Organization / System) driving which picker shows; a system picker
  backed by a new `GET /api/admin/systems` lookup (search-by-name, same shape
  as the organizations lookup). Grants panel unchanged visually — its user
  search just widens with the scope. Bundle rebuilt via the node:14
  container, dist committed.

## Local dev

`ReferenceDataSeeder` seeds System 1 ("Local Health System") containing
organizations 1 and 2 through `SystemOrganizations`, so a system-owned client
is testable locally (org 933 / SSO Organization stays system-less — the
degenerate case stays covered). Existing seeded clients become
`owner_type = 'organization'` via the backfill, no seeder changes needed
beyond the column rename in explicit attributes.

## Testing

- Migration/backfill: existing factory rows and seeded clients land as
  org-owned with identical behavior (the whole existing suite is the
  regression net — it must stay green with only mechanical
  `organization_id` → owner updates in test fixtures).
- Scope helpers: org-owned scope = own org; system-owned scope = system's
  orgs; system-less org degenerates to own-org-only.
- Manager: owner validation (unknown org/system, missing/both owners),
  default-department rules (org-owned belongs-to-org, system-owned must be
  null, re-parent guard), grants scope validation across a system.
- Login: system-owned existing-user session org; system-owned new-JIT
  rejection with `unrouted_user`; org-owned paths unchanged.
- API/CLI/portal: owner payloads, XOR flags, systems lookup, Dusk on the
  reworked dialogs (create org-owned + create system-owned).

## Rollout

Branch `client-ownership`, stacked on `milestone-bonus` (login) with a
companion `website_admin` branch stacked on `admin-portal-sso` for the dialog
changes — same PR pattern. Deploy is inert: every existing client backfills
to org-owned and behaves identically; system ownership only matters once an
admin creates such a client.
