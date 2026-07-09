# Admin Portal SSO — Design

Bonus effort beyond the fixed-bid milestones: put the legacy admin portal
(`website_admin`) itself behind SAML SSO, so Apex employees sign into it through the
company IdP instead of (or alongside) the portal's static-salted-MD5 password check
(`doLogon.php`). Feature-flagged so it can be switched on and off at runtime without a
deploy.

Two codebases again: the SAML/identity side in this repo, a small callback script and
login button in `website_admin` (sibling checkout).

## The flag

A new boolean column `admin_portal` on `saml_clients` (default false; settable via
`saml:client create/update --admin-portal`, surfaced in the admin API and portal page).
One client row — slug `apex-admin`, pointed at the internal IdP — carries it.

The client's existing `enabled` state **is** the feature flag. `saml:client disable
apex-admin` (or the portal page's disable button) switches admin SSO off at runtime;
the SP-login and ACS endpoints already 404 disabled clients server-side, and the
portal's SSO button hides itself (below). No env vars, no deploy, one source of truth.

An `admin_portal` client is excluded from `/sso/lookup` email-domain routing and the
domain-claim machinery entirely — it never participates in customer SP-initiated flows.

Password login (`doLogon.php`) stays fully functional regardless of the flag. SSO is
additive; if the IdP or the login app is down, admins still get in. Retiring the MD5
path is a separate, later decision.

## Trust model

Portal admins are `Employees` rows (keyed by corporate `Email`, `Active = 'Y'`).
Membership in that table is the authorization — the ACS matches the asserted email
against `Employees` and **fails closed**: no row, or an inactive row, means the
standard rejection page with a logged `reason` (`no_employee_match`). There is no JIT
provisioning and no fallback to the `Users` table on an `admin_portal` client, and no
`Auth::login()` either — a Laravel session in the login app means nothing to the
portal. Existing per-page `Level`/`SalesRep` checks keep working untouched, since they
read the same Employees row.

The handoff into the portal's own session realm reuses the milestone-4 service-token
bridge rather than teaching the login app to forge `ApexAdmin` sessions:

- **One-time token.** After a successful match the ACS stores
  `{employee_id, name}` in the cache under `admin_sso:token:{64-char random}`,
  TTL 60 seconds, and 302s the browser to `{portal_url}/ssoLogon.php?token=...`.
- **Server-side redemption.** `POST /api/admin/sso-handoff/redeem` (behind
  `admin.api`, same `ADMIN_API_TOKEN`) atomically deletes-and-returns the payload;
  a second redemption of the same token fails. Possession of the redirect URL alone
  is useless without the portal's server-side API credential.

Rejected alternatives: the login app writing the `ApexAdmin` Redis session directly
(couples it to the portal session handler's ID-encryption and serialization internals —
mousetrap-y, kept as a fallback if the callback approach hits deploy friction);
portal-side php-saml (duplicates the whole milestone-2 stack in legacy PHP with no test
harness).

## ACS branch (this repo)

`SamlController::acs` gains one branch: when `$client->admin_portal`, skip the
`SamlUserProvisioner` / session establishment entirely and call an
`AdminSsoHandoff` service — look up the Employee, mint the token, redirect. Everything
before the branch (signature validation, replay guard, cert-expiry warning, identity
extraction via the client's `attribute_map`) is shared with the normal flow unchanged.
The success log line records `employee_id` rather than `user_id`.

New config: `saml.admin_portal_url` (env `ADMIN_PORTAL_URL`) — the base the ACS
redirects to. Local: the website container's admin path; prod:
`https://www.apexinnovations.com/admin`.

## Portal side (`website_admin`)

New `ssoLogon.php`, mirroring `doLogon.php`'s contract:

1. `require SESSIONCONFIG.php` — native `ApexAdmin` session, nothing custom.
2. Redeem `$_GET['token']` server-to-server via the same curl/env pattern as
   `doSSOClients.php`'s `loginApi()` helper.
3. On success: `session_regenerate_id(true)` (fixation), set `$_SESSION['AdminID']`
   and `$_SESSION['AdminName']`, set `$_SESSION['SSOLogin'] = true`, insert the
   `AdminEvents` type-1 (login) row via `apx_AdminEvent` — insert failure is access
   denied, exactly like `doLogon.php` — then redirect to `Home.php`.
4. Any failure: redirect to `Home.php?error=Access Denied`, same as password failures.

`HEADER.php` changes:

- A "Sign in with SSO" button beside the existing login form, linking to the login
  app's `/saml/apex-admin/login`. Shown only when the flag is on: a small server-side
  check through the API bridge (`GET /api/admin/saml-clients/apex-admin`, result cached
  in the session for 60 seconds) — if the check errors or the client is missing or
  disabled, the button hides and nothing else is affected.
- The 90-day `PasswordLastChanged` forced-reset redirect is skipped when
  `$_SESSION['SSOLogin']` is set — password age is meaningless in an SSO session.

## Testing

Login app (phpunit, MySQL):

- `admin_portal` flag: migration, CLI `--admin-portal`, API create/update/detail.
- ACS matcher: active Employee matches; inactive and unknown emails rejected with
  `no_employee_match`; a non-admin client's flow is completely unaffected; no `Users`
  row is created or touched.
- Handoff: token minted with TTL and correct payload; redeem returns it exactly once
  (second call 404s); expired token 404s; redeem requires the admin token.
- `/sso/lookup` never routes to an `admin_portal` client.

End-to-end (Dusk + mock IdP + website container):

- Seed a second mock client `local-admin-idp` with `admin_portal = true` (the mock
  IdP's static `user1` email seeded as an active Employee, alongside `dev.admin`).
  The `kristophjunge/test-saml-idp` image is configured for a single SP ACS URL, so
  this likely means a second container instance (`mock-idp-admin`) in
  `docker-compose.yml` pointing at `/saml/local-admin-idp/acs` — cheap, and keeps the
  customer-flow mock untouched.
- Flag on: click the SSO button on the portal login form → mock IdP → land on
  `Home.php` with the admin menu rendered; `AdminEvents` has the type-1 row.
- Flag off (`saml:client disable local-admin-idp`): button absent, `/saml/local-admin-idp/login` 404s.

## Rollout

1. Deploy the login app (inert: no `admin_portal` client exists; migration adds the
   column automatically via the `migrations_login` startup migrate).
2. Deploy `website_admin` (inert: flag off, button hidden, `ssoLogon.php` unreachable
   without a token).
3. `saml:client create` the `apex-admin` client against the internal IdP metadata,
   `--admin-portal`, verify with a test employee, `enable` when satisfied.
4. Switch off any time: `saml:client disable apex-admin` or the portal toggle.
