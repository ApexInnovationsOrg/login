# SSO Admin UX — Design

Follow-on to milestone 4 (SSO admin tools), deliberately beyond the fixed-bid hours:
make the SSO Clients admin experience enjoyable rather than merely functional. Admins
should never have to remember an Organization ID, a Department ID, or a user's exact
login — the relationships already exist; the UI should walk them.

Two codebases, same trust model as milestone 4: the browser talks only to the portal's
`doSSOClients.php` bridge; the bridge talks to the login app's admin API with the
service token.

## Page architecture

`website_admin/SSOClients.php` keeps its house shell — `$PageName`, `HEADER.php`, the
`$_SESSION['AdminID']` gate, `FOOTER.php` — but its authorized body reduces to
`<div id="ssoClientsVue"></div>`.

The experience is a Vue 2 + Element UI component, `SSOClientsApp.vue`, in the portal's
existing `admincomponents` app, registered in `main.js`'s `renderVue()` mount list —
the same pattern `Departments.php` and `Organizations.php` already use. Element UI is
already bundled and loaded on every admin page via `FOOTER.php`.

The current jQuery implementation is retired wholesale, not maintained in parallel.
This also retires the string-concatenation escaping problem class: Vue text
interpolation escapes by default.

## Lookup endpoints (login app)

Three read-only endpoints behind the existing `admin.api` middleware, mirroring the
CLI wizard's queries (`SamlClientCommand::wizardOrganizationOptions/DepartmentOptions`):

```
GET /api/admin/organizations?q=          -> {data: [{id, name}]}          limit 25, Name like %q%, ordered by Name
GET /api/admin/organizations/{id}/departments
                                         -> {data: [{id, name}]}          Active='Y', ordered by Name
GET /api/admin/saml-clients/{slug}/users?q=
                                         -> {data: [{login, first_name,
                                             last_name, department}]}     limit 25, scoped to the client's
                                                                          organization via Department join;
                                                                          q matches Login or First/LastName
```

GETs carry no `X-Acting-Admin` requirement (reads are not audited). Controllers stay
transport-only; queries live in small dedicated controller methods (no new manager
surface — these are lookups, not writes).

## Bridge additions (doSSOClients.php)

Three new cases in the existing action switch — `org_search`, `dept_list`,
`user_search` — each a mechanical proxy to the endpoints above, same response
bridging (`{success, retData}`) the page already consumes.

## The experience (practical-polish tier)

- **Client list**: `el-table` — name, slug, email domains as tags, enabled/disabled
  status tag, certificate expiry with a red `EXPIRING` tag (30-day window). Row
  actions: edit, enable/disable. Empty state with a "create your first client" hint.
- **Create/edit form** (an `el-dialog`; the metadata and grants panels live in an
  inline detail section that appears when a row is selected):
  - Organization: `el-select` with remote search — type a name, see "Name (ID)",
    never type a raw ID.
  - Default department: dependent `el-select`, populated from the chosen org, with an
    explicit "None — users choose at finish-account" option mapping to null (same
    semantics as the CLI wizard).
  - Slug: auto-derived from name on create (placeholder shows the derivation), locked
    on edit.
  - Email domains: editable tag list (type, enter, tag appears; click x to remove).
  - Attribute map: three fields grouped under a hint that Entra/ADFS send full claim
    URIs (the milestone-2 lesson, surfaced where the admin needs it).
  - 422 error bags map to per-field form errors (`el-form-item` error prop),
    including dotted keys (`attribute_map.email`).
- **IdP metadata**: dialog with paste textarea; on success shows parsed entity ID,
  SSO URL, and cert expiry; failures show the API's message inline.
- **Enable/disable**: `el-popconfirm`; success toast (`$message`) naming the client.
- **Grants panel** (detail view): user autocomplete via `user_search` showing
  "First Last — login (Department)"; granted users render as removable tags with the
  grantor in a tooltip; add/remove saves via the existing replace semantics.
- **States**: loading indicators on every remote call; disabled submit while busy;
  toasts for success; a single error banner only for unreachable-service failures.

## Build and asset story

`admincomponents` builds locally (`npm ci && npm run build` in
`website_admin/admincomponents`) and its `dist/` is committed — the portal's existing
convention. No pipeline changes.

**Plan-time checkpoint (blocking):** verify the Vue-CLI-era toolchain still builds on
node 24 before any component work. Small pins are acceptable; if it needs major
surgery, stop and reassess the approach rather than yak-shaving the build.

## Testing

- **Login app**: feature tests for the three lookup endpoints (query filtering,
  org scoping, active-department filtering, limit, auth via middleware group).
- **Dusk**: rewrite the five SSO-page browser tests against the new Element UI DOM
  (same behaviors: list, create-with-422-then-success, enable/disable, grants
  add/remove, plus the smoke), and add one new test proving the relationship UX:
  search the org picker, select a result, verify the form carries the org and the
  department dropdown populates.
- `make e2e` (SAML flows, session handoff) unaffected.

## Branch strategy

Login-app work on `milestone4-ux`, stacked on `milestone4-admin` (keeps the reviewed
milestone core separable). Portal work continues as commits in the sibling
`website_admin` repo (dist rebuilt and committed alongside src changes).

## Out of scope

- Visual redesign beyond Element UI defaults (no custom theme work).
- Dashboard-tier extras (setup-progress indicators, copy buttons, avatars) — noted as
  a possible later tier.
- Touching any other admin page, or upgrading Vue/Element UI versions.
- New write endpoints — the three additions are lookups only.
