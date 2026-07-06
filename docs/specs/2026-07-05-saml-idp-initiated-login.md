# SAML IdP-Initiated Login — Design

Milestone 2 of the SSO contract: tailored SAML connection for identity-provider-initiated
login, including the Okta-based login flow. First client is an Okta customer; an
Azure/Entra ID client follows immediately after and must be onboardable with
configuration only (no code changes).

## Approach

Direct integration with `onelogin/php-saml` (the canonical, maintained PHP SAML engine)
behind a thin application-owned layer. No wrapper package: the previous prototype's
wrapper (`aacotroneo/laravel-saml2`) was abandoned upstream, and the client model here
must be shaped for Apex's organization/department hierarchy, which milestones 3-5
build on directly.

An identity broker (Keycloak/authentik) was considered and rejected at this scale:
it adds an operated service to the handoff burden while still requiring all the
provisioning and legacy-session work to be written here. Revisit if the IdP count
grows toward dozens or a customer requires a protocol the app does not speak.

## Data model

New table via a standard Laravel migration (migrations now run against the shared
production database for app-owned tables). App-owned tables use Laravel conventions,
not legacy conventions.

```
saml_clients
  id                bigint PK
  name              string          display name, e.g. "MD Anderson"
  slug              string unique   URL-safe identifier; ACS route key
  enabled           boolean         default true
  idp_entity_id     string          from customer IdP metadata
  idp_sso_url       string          IdP SSO endpoint (used by milestone 3; captured now)
  idp_certificate   text            IdP x509 signing certificate (PEM)
  organization_id   int             Apex Organization for this client's users
  department_id     int nullable    default landing department; null/0 = finish-account flow
  jit_enabled       boolean         per-client just-in-time provisioning
  attribute_map     json            claim names for email / first_name / last_name;
                                    defaults match Okta names; Entra claim URIs are
                                    just different values
  created_at / updated_at
```

Deliberate non-table decisions:

- **SP identity is global** — one SP key/cert pair and entity ID in env/config, shared
  by all clients. Per-client SP certs multiply admin surface with no security gain
  at this scale.
- **Replay protection uses Redis, not a table** — processed assertion IDs are
  check-and-set with a TTL matching the assertion validity window; self-cleaning.

## Components

| Unit | Responsibility | Depends on |
|---|---|---|
| `SamlClient` (model) | client config row | — |
| `SamlSettingsFactory` | client row + global SP config → php-saml settings array; pure, no I/O | config |
| `SamlClientManager` | client lifecycle: create, update, enable/disable, **IdP-metadata parsing** (entity ID / SSO URL / cert out of pasted XML), certificate expiry status. All validation lives here. | `SamlClient` |
| `SamlUserProvisioner` | validated attributes + client → `User`: match by `Users.Login`, JIT-create when enabled (explicit legacy-column values), reject unknown/disabled | `User` |
| `SamlController` | HTTP orchestration: resolve client, process/validate response, replay guard, delegate to provisioner, establish session, redirect | all above |
| `saml:client` artisan command | thin CLI skin over `SamlClientManager` (argument parsing + table output only) | `SamlClientManager` |

The milestone-4 admin dashboard calls the same `SamlClientManager` methods; nothing
about client lifecycle exists twice. The command's create/list output includes the
derived ACS and metadata URLs and certificate expiry dates.

## Routes

- `POST /saml/{client}/acs` — Assertion Consumer Service; CSRF-exempt (external POST
  by design). Client resolved by slug.
- `GET /saml/{client}/metadata` — per-client SP metadata XML for customer IT admins.

ACS URLs are always derived (`{APP_URL}/saml/{slug}/acs`), never stored.

## Login flow (ACS)

1. Resolve enabled client by slug; unknown/disabled → friendly error page, logged.
2. Build settings via `SamlSettingsFactory`; `onelogin/php-saml` validates signature,
   audience, timestamps, destination. **Signed assertions required.**
3. Replay guard: assertion ID check-and-set in Redis; duplicates rejected.
4. Extract email / first / last name via the client's `attribute_map`.
5. Match `Users.Login` by email.
   - No match, JIT enabled → create user: `DepartmentID` = client `department_id` ?? 0
     (organization association flows through the department; there is no org column
     on `Users`), `CredentialID` 0, `Password` = `Hash::make()` of a random string,
     `Disabled='N'`, and **explicitly `PasswordChangedByAdmin='N'`** (the production
     column default is `'Y'`, which would trap SSO users in the forced-reset flow).
   - No match, JIT disabled → friendly "contact your administrator" page, logged.
6. Matched user with `Disabled='Y'` → rejected, same as password login.
7. Session establishment (the invariant contract with the legacy site): `Auth::login()`,
   all four legacy session keys (`userId`/`userID`/`userName`/`Username`), the `SAML`
   session flag, **`Organization` session key = the client's `organization_id`** (the
   finish-account flow lists departments from this key), `LastLoginDate` update.
8. Redirect: unfinished user (`DepartmentID == 0 || CredentialID == 0`, loose compare)
   → `/finishAccountCreation`; otherwise → `redirect()->away()` to the MyCurriculum
   URL (plain 302 — IdP-initiated flows cannot follow Inertia's 409 handoff).

Every rejection logs structured context (client slug, IdP entity ID, assertion ID,
reason) to the standard log channel. Error pages are non-technical Blade views on a
simple standalone layout (avoids rebuilding the committed frontend bundle; can be
ported to Inertia during the milestone-4 UI work).

## SP configuration

- `SAML_SP_ENTITY_ID` — defaults derived from `APP_URL`.
- `SAML_SP_CERT` / `SAML_SP_KEY` — single PEM pair. Local/E2E use a dev-only
  self-signed pair generated by `make env`; production's pair is generated at deploy
  setup and lives in the ECS task environment, never in git.

## Onboarding runbook (drafted during Okta validation; becomes milestone-6 handoff doc)

1. Apex: `php artisan saml:client create ...` → prints ACS + metadata URLs.
2. Customer IT: create SAML 2.0 app integration in Okta/Entra; paste ACS URL and
   entity ID (or import metadata URL); map attributes.
3. Customer sends IdP metadata XML; Apex: `saml:client update --metadata=...` —
   parser extracts entity ID, SSO URL, certificate. No manual certificate handling.
4. Test user launches the app from the IdP tile → lands on MyCurriculum.

Certificate expiry: `saml:client list` shows expiry dates; a warning is logged when
an assertion is signed by a certificate within 30 days of expiry.

## Testing

- **Feature tests**: real signed SAML responses built in-test with a fixture IdP
  keypair (full pipeline, genuine signatures). Coverage: happy-path match, JIT
  on/off, disabled user, unknown client, disabled client, bad signature, expired
  assertion, replayed assertion, attribute-map variance (Okta-style names vs
  Entra-style claim URIs), unfinished-user redirect, legacy session keys.
- **Unit tests**: `SamlSettingsFactory`; metadata parser against real Okta and Entra
  metadata samples.
- **E2E**: mock SAML IdP container (SimpleSAMLphp-based) in the compose stack, seeded
  as a `saml_clients` row by `make db`; `tests/e2e/saml-login.sh` drives IdP-initiated
  login and asserts the legacy site recognizes the session.
- **Okta validation**: manual checklist against the Okta integrator account (doubles
  as the runbook draft). Not in CI.

## Out of scope (later milestones)

- SP-initiated flow and email-domain routing (milestone 3) — `idp_sso_url` is already
  captured; a `email_domains` column arrives with that milestone.
- Admin UI (milestone 4) — builds on `SamlClientManager`.
- Attribute-based org/department/role routing (milestone 5) — builds on `attribute_map`.
- Single Logout (not contracted; logout remains app-local).
