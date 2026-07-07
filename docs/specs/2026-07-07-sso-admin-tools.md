# SSO Admin Tools — Design

Milestone 4 of the SSO contract: administrative tools for Apex personnel to configure
client SSO settings. The admin experience lives where Apex admins already work — the
legacy site's admin portal (`website_root/admin`) — backed by a JSON API on the login
application so `SamlClientManager` remains the single validated write path shared with
the `saml:client` CLI.

Two codebases: the API in this repo, the screens in `website_root` (sibling checkout).

## Trust model

The admin portal authenticates its own users against the Employees schema — a separate
auth world from the shared `Users` session, with essentially flat RBAC (portal access ≈
full access). The login app does not attempt to validate that world. Instead:

- **A static service token authorizes.** `ADMIN_API_TOKEN` in both apps' env (the same
  way the session key is already shared). The browser never sees it: admin pages talk to
  a portal-side action script, which calls the login API server-to-server.
- **`X-Acting-Admin` attributes.** Every mutating request carries the acting admin's
  employee identity in this header; the API requires it (400 if absent on mutations) and
  writes it to the audit log. It grants nothing — the token does that.

Rejected alternatives: browser-direct API calls (would require the login app to validate
Employees auth, or expose the token); an admin UI inside the login app (puts SSO admin in
a different place from every other admin task).

## API surface (login app)

All under `/api/admin`, JSON in/out, guarded by the `admin.api` middleware:

```
GET    /saml-clients                      list: name, slug, enabled, jit, org/dept,
                                          domains, cert expires_at + expiring flag
GET    /saml-clients/{slug}               full detail incl. ACS/metadata URLs,
                                          attribute map, grants count
POST   /saml-clients                      create (name, slug?, organization_id,
                                          department_id?, jit_enabled?, email_domains?)
PATCH  /saml-clients/{slug}               partial update of the above + attribute_map
POST   /saml-clients/{slug}/idp-metadata  body: {xml} — runs updateFromIdpMetadata
POST   /saml-clients/{slug}/enable
POST   /saml-clients/{slug}/disable
GET    /saml-clients/{slug}/grants        list grants for the client's organization
PUT    /saml-clients/{slug}/grants        replace grants (list of Users.Login values)
```

Controllers are transport only: translate HTTP ↔ `SamlClientManager` (and the grants
model). No validation logic in controllers — `ValidationException` surfaces as a
standard Laravel 422 error bag the portal JS renders as field errors. Unknown slug → 404.

## Middleware: `admin.api`

- `Authorization: Bearer <token>` compared to `config('admin.api_token')` with
  `hash_equals`; 401 on mismatch.
- If the env var is unset: 503 + a logged error — a misconfigured deploy fails loud and
  closed, never open.
- Mutating verbs require `X-Acting-Admin` (non-empty string); 400 otherwise.
- No rate limiting: server-to-server behind a shared secret.

Every mutation logs one structured line: acting admin, method+path, slug, and the changed
field *names* (values only for `enabled` and `email_domains`, which are the operationally
interesting ones).

## Grants model (designed now, consumed later)

Approved direction: admins may delegate SSO control to specific customer users, scoped to
their organization. Milestone 4 ships the model and admin-side management; the end-user
experience that *consumes* grants ("manage my org's SSO" in the login app) is explicitly
future work.

```
sso_grants
  id               bigint PK
  user_id          int            Users.ID
  organization_id  int
  granted_by       string         acting admin identity (Employees world — no FK)
  created_at / updated_at
  unique (user_id, organization_id)
```

API validation: the user exists and belongs to the organization (via Department →
Organization). Flat by design — no roles or scopes — matching the portal's flat RBAC.

## Admin screens (website_root/admin)

One page + one action script, written in the portal's existing house style (Admin.inc
bootstrap, jQuery, `ajax/`-style action endpoint):

- **Clients table**: name, slug, enabled, domains, cert expiry with an EXPIRING badge
  (30-day window, same as the CLI).
- **Create/edit form**: name, slug (create only), organization, default department, JIT
  toggle, domains (comma-separated), attribute-map fields.
- **IdP metadata**: paste-in textarea (file upload optional if the house pattern makes it
  cheap) → the metadata endpoint; shows resulting entity ID / SSO URL / cert expiry.
- **Enable/disable** buttons with confirmation.
- **Grants panel** (detail view): list grants, add by `Users.Login` lookup constrained to
  the client's organization, remove.

The action script holds the bearer token (from the portal's env) and the acting-admin
identity from the portal's session; it proxies JSON to the login API and passes 422 error
bags through for inline field rendering.

## Testing

- **Login app (feature tests)**: middleware matrix (no token, wrong token, env unset →
  503, missing X-Acting-Admin on mutation → 400); every endpoint's happy path,
  validation failure, uniqueness conflict, unknown-slug 404; grants list/replace incl.
  org-membership validation. Existing CLI tests stay green untouched — evidence the
  manager remains the shared path.
- **E2E — Laravel Dusk**: browser tests in this repo drive the real admin page across
  the compose network (`http://website/admin/...`) — table rendering, create/edit with
  inline 422 errors, metadata upload, enable/disable, grants panel. Setup: a
  `selenium/standalone-chrome` service in docker-compose + `DUSK_DRIVER_URL`. Dusk sees
  the actual jQuery behavior a curl script cannot. Local-only gate (like `make e2e`);
  CI does not run the compose environment.
  - **Plan-time checkpoint**: the tests must log into the portal, so the local website
    container needs a seeded Employees-schema admin account (or known dev credentials).
    Confirm this is seedable before committing to the Dusk path in the plan.
- The legacy repo has no unit-test harness; no attempt to add one in this milestone.

## Budget shape (15h)

API + middleware + grants ≈ 7h; admin page + action script ≈ 5h; E2E + docs ≈ 2h;
slack ≈ 1h. Feasible because the API adds transport, not validation — all rules already
live in `SamlClientManager`.

## Out of scope

- End-user-facing grant consumption (a granted user managing their org's SSO) — future
  milestone; the grants table and admin management exist so it can be added without
  rework.
- Any RBAC beyond portal-access + token (matches the portal's reality).
- Attribute-based routing configuration (milestone 5).
- Modernizing or testing the legacy admin portal beyond the one new page.
- Refactoring milestone 3's shell-script E2E (saml-login.sh, session-handoff.sh) onto
  Dusk — endorsed as a follow-up once Dusk exists, not in this budget.
