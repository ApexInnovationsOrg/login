# SSO Client Onboarding Runbook

This runbook describes how to onboard a new customer to SAML SSO (IdP-initiated
login) using the `saml:client` artisan command. Okta is covered in detail as the
first supported identity provider; Entra ID (Azure AD) follows the same
procedure with provider-specific notes called out below.

All commands are run inside the application container, e.g.:

```bash
docker compose exec login php artisan saml:client <action> ...
```

## Overview

Each customer is represented by one row in `saml_clients`, identified by a
unique `slug`. A client has:

- An **ACS URL** and **metadata URL** we give to the customer.
- An **entity ID**, **SSO URL**, and **certificate** we get from the customer's
  identity provider (applied from their metadata XML).
- An **organization** (and optionally a default **department**) that
  determines which Apex organization new users are attached to.
- A **JIT (just-in-time) provisioning** flag controlling whether unknown users
  are auto-created on first login.
- An **attribute map** telling us which assertion attribute names carry the
  user's email, first name, and last name.

A client only accepts logins once it is both enabled and has real IdP metadata
applied — `saml:client create` deliberately leaves a new client disabled with
placeholder IdP fields until those two steps are done.

## Onboarding steps

### Step 1: Create the client

```bash
php artisan saml:client create --name "Acme Health" --org 42 --jit --domains=acmehealth.com
```

Options:

- `--name=` — display name (required).
- `--slug=` — explicit slug; defaults to a slugged version of `--name`.
- `--org=` — Apex organization ID to attach users to (required).
- `--department=` — default department ID for JIT-created users. Omit this to
  route new users through the finish-account-creation flow instead of
  pre-assigning a department.
- `--jit` / `--no-jit` — enable or disable just-in-time provisioning. A client
  created without either flag defaults to JIT disabled.
- `--domains=` — comma-separated email domains for SP-initiated SSO routing
  (e.g. `--domains=acmehealth.com,acme-health.org`). SP-initiated routing only
  activates once a client has email domains assigned; a client with no domains
  is IdP-initiated only (its users start login from the IdP's dashboard, not
  from `/sso/lookup`). On `update`, `--domains=` replaces the entire list;
  passing an empty value clears it (disabling SP-initiated routing for that
  client).

The command prints the information the customer's IT admin needs:

```
Created Acme Health (acme-health). Give the customer:
  ACS URL:      <APP_URL>/saml/acme-health/acs
  Metadata URL: <APP_URL>/saml/acme-health/metadata
  Entity ID:    <saml.sp.entity_id>
Then: saml:client update acme-health --metadata=<their-metadata.xml> && saml:client enable acme-health
```

Send the customer the **ACS URL**, **Metadata URL**, and **Entity ID**. Their
IT admin can either configure their IdP directly from these three values, or
fetch `GET <Metadata URL>` from their own tooling if it can consume SP
metadata XML directly.

#### Interactive alternative: `--wizard`

Instead of remembering the numeric organization and department IDs, run:

```bash
docker compose exec login php artisan saml:client create --wizard
```

The wizard prompts for the display name and slug, then lets you **search
organizations and departments by name** (no IDs to look up), asks whether to
enable JIT provisioning, and offers to set custom attribute names for providers
that do not use Okta's defaults (e.g. Entra/Azure). It creates the same disabled
client and prints the same ACS/metadata/entity handoff block as the flag-based
command, so Step 2 onward is identical. The wizard needs an interactive terminal;
use the flag-based form above for scripted/non-interactive creation.

### Step 2: Get IdP metadata from the customer

Ask the customer for their **IdP metadata XML** (an exported file, or a URL
you can fetch it from). This must contain their entity ID, SSO URL, and
signing certificate. See the Okta and Entra walkthroughs below for exactly
where to get this from each provider's admin console.

### Step 3: Apply the metadata and enable the client

```bash
php artisan saml:client update acme-health --metadata=/path/to/their-metadata.xml --domains=acmehealth.com
php artisan saml:client enable acme-health
```

`update --metadata=` parses the customer's metadata XML and fills in
`idp_entity_id`, `idp_sso_url`, and `idp_certificate` on the client record.
`update` also accepts `--name=`, `--org=`, `--department=`, `--jit`/`--no-jit`
if any of those need to change at the same time.

The client will not accept logins until `enable` has been run — `create`
leaves it disabled on purpose so an unfinished configuration can never accept
traffic.

### Step 4: Confirm the attribute mapping

By default the app expects the assertion to carry attributes named `email`,
`firstName`, and `lastName` (this is Okta's default attribute naming). If the
customer's IdP sends different attribute names (Entra ID uses URI-style claim
names, see below), update the client's attribute map before go-live — this is
a configuration change only, no code change is required. Confirm this during
the Okta/Entra walkthrough below rather than guessing; a wrong mapping
surfaces as a `no_email_attribute` rejection (see Troubleshooting).

Once metadata is applied and the client is enabled, ask the customer to
assign a test user to the app in their IdP and click through IdP-initiated
login to confirm end to end before rolling out broadly.

## Okta walkthrough

1. In the Okta admin console, create a new **SAML 2.0** app integration.
2. General settings:
   - **Single sign-on URL**: the client's ACS URL from `saml:client create`
     (e.g. `https://login.apexinnovations.com/saml/acme-health/acs`).
   - **Audience URI (SP Entity ID)**: the Entity ID from `saml:client create`
     (the value of `saml.sp.entity_id`, shared by all clients).
   - Leave "Use this for Recipient URL and Destination URL" checked (default) —
     these should match the ACS URL.
3. Attribute Statements — map these exactly, since they are the app's default
   attribute names:
   - `email` → user's email
   - `firstName` → user's first name
   - `lastName` → user's last name
4. Assign the app to a test user before finishing setup.
5. Save, then download the app's **IdP metadata** (Okta provides a "SAML
   Setup Instructions" link and a metadata URL/download on the app's Sign On
   tab) and hand it to Step 3 above (`saml:client update --metadata=`).
6. Have the test user click the app tile from their Okta dashboard (this is
   the IdP-initiated flow this app supports) and confirm they land on either
   the finish-account screen (new JIT user, no department assigned) or
   directly on MyCurriculum (existing user).

## Entra ID (Azure AD) walkthrough

Entra clients are onboarded with the same four steps above — there is no code
difference between providers. Two things differ in practice:

- Entra's enterprise application SAML configuration uses the same
  **Reply URL (ACS URL)** and **Identifier (Entity ID)** fields as Okta's
  Single sign-on URL / Audience URI; populate them the same way.
- Entra emits claims using URI-style names by default (e.g.
  `http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress`) rather
  than the short names Okta uses. Set the client's attribute map to match
  those claim URIs — this is the same config-only attribute map mentioned in
  Step 4 above, just with different values than Okta's defaults.

## Certificate expiry

`saml:client list` shows each client's IdP certificate expiry date. The
application also logs a warning on every successful login if the assertion
was signed by a certificate expiring within 30 days, so it will surface in
logs before `list` is checked manually. When a customer rotates their signing
certificate, re-run:

```bash
php artisan saml:client update <slug> --metadata=<their-new-metadata.xml>
```

## Admin portal

SSO clients can also be managed from the legacy admin portal at
`/admin/SSOClients.php` instead of the CLI: list clients, create a new one
(with inline validation errors from the API), apply IdP metadata, enable or
disable a client, and manage its organization grants (the "SSO managers"
list of users permitted to administer that org's SSO settings). Organizations,
departments, and grantable users are chosen through searchable pickers — no
numeric IDs to look up, same as the CLI's `--wizard` mode. The portal
page is a thin UI over this app's admin API — it does not talk to the
database directly. Grants belong to the organization, not the individual
client, so SSO clients that share an organization also share one grant list.

The API it consumes lives in this app at `/api/admin/saml-clients` (see
below). Both this application and the admin portal's application need
`ADMIN_API_TOKEN` set (the same value in both `.env` files) for the portal
to authenticate its API calls. The CLI (`saml:client ...`) remains fully
equivalent to the portal for every operation the portal supports — use
whichever is more convenient.

**Rollout note:** migrations run automatically when a login container starts
(the image entrypoint runs `php artisan migrate --force --isolated`). This is
safe against the shared database because the login app tracks its migration
history in its own `migrations_login` table — the shared `migrations` table
belongs to other applications and is never read or written. No manual
migration step is needed; a failed migration stops the new container before
it serves, leaving the previous deployment running.

## Admin portal SSO (apex-admin)

The admin portal itself can sit behind SSO. A SAML client marked
`--admin-portal` asserts *Employee* identities (the portal's own auth table):
the ACS matches `Employees.Email` (active rows only, never JIT-provisions),
mints a 60-second single-use token, and redirects to the portal's
`ssoLogon.php`, which redeems it server-to-server (`POST
/api/admin/sso-handoff/redeem`, same `ADMIN_API_TOKEN` bridge as the SSO
Clients page) and establishes the normal portal session. Password login is
unaffected; both paths coexist.

**The feature flag is the client's `enabled` state.** `saml:client disable
apex-admin` (or the portal toggle) turns admin SSO off at runtime: the
login/ACS endpoints 404 and the portal's "Sign in with SSO" button hides
itself within a minute. Admin-portal clients cannot claim email domains and
never appear in `/sso/lookup` routing.

Rollout: deploy login app, deploy website_admin, then
`saml:client create --name="Apex Admin" --org=<org> --admin-portal`,
apply the internal IdP's metadata, verify with a test employee, and
`saml:client enable apex-admin`. Set `LOGIN_SSO_CLIENT=apex-admin` in the
portal's env. Local dev uses the second mock IdP (`mock-idp-admin`,
client `local-admin-idp`, login user1/user1pass).

## Troubleshooting

The application logs a `reason` value on every rejected SAML login. Use this
table to translate a logged reason into a cause and fix.

| Log `reason`                 | Cause                                                                                          | Fix                                                                                                   |
|-------------------------------|--------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `invalid_response`            | Signature, audience, timestamp, or destination validation failed — wrong certificate, clock skew between IdP and us, wrong ACS/entity ID configured at the IdP, or an unsigned assertion. | Re-check the IdP's SSO URL/Audience/ACS configuration against the values from `saml:client create`; re-apply current metadata with `saml:client update --metadata=`; confirm the IdP signs assertions. |
| `replayed_assertion`          | The same assertion ID was submitted to the ACS more than once. Usually a double form submission (e.g. browser back/refresh) or a replay attempt. | Have the user retry login from the IdP tile. If this recurs for the same user/IdP, investigate for an actual replay attempt. |
| `no_email_attribute`          | The assertion did not carry an email in the attribute named by the client's attribute map, and no usable NameID was present either. | Fix the customer's attribute mapping (Okta attribute statements or Entra claim names) so the app's mapped `email` field is actually sent; verify with `saml:client list`/`update` that the attribute map matches what the IdP sends. |
| `disabled_user`               | The matched Apex user has `Disabled='Y'`.                                                       | Reactivate the account in the admin site if the user should regain access.                              |
| `unknown_user_jit_disabled`   | No existing Apex user matches the login, and JIT provisioning is off for this client.            | Either enable JIT (`saml:client update <slug> --jit`) or pre-create the user account manually before the customer's users start logging in. |
| `no_employee_match`           | SAML login on an admin-portal client asserted an email with no active Employees row.             | Verify the employee's Email and Active='Y' in the Employees table; admin SSO never auto-creates accounts. |

## Validation checklist (run once against the Okta integrator account)

This checklist has not yet been executed. It must be run manually by an
operator with access to the Okta integrator account before this SSO flow is
considered validated end to end; no step below should be treated as passed
until it has actually been performed and its outcome recorded here.

Using a client created with:

```bash
php artisan saml:client create --name "Okta Validation" --org 1 --jit
```

- [ ] Create the Okta app integration per the walkthrough above; export its
      metadata; apply it with `saml:client update`.
- [ ] Enable the client; assign a test user to the app in Okta.
- [ ] Click the Okta tile as the test user → confirm the user lands on
      `/finishAccountCreation` (JIT-created user, no department assigned).
- [ ] Complete the finish-account flow → confirm the user lands on
      MyCurriculum.
- [ ] Click the Okta tile again as the same user → confirm they go straight
      to MyCurriculum (existing-user path, no finish-account detour).
- [ ] Un-assign the test user from the Okta app, disable JIT on the client,
      then have a new (never-logged-in) user click the tile → confirm they
      see the friendly rejection page rather than an error or blank
      response.
