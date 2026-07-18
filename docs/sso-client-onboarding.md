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

### Organization vs system ownership

A client is owned by a single organization (the default; users are placed via
the client's default department or the finish-account flow) or by a hospital
system (`saml:client create --system=<id>`), which spans every organization in
that system. System-owned clients cannot hold a default department; new users
on them are rejected (`unrouted_user` in the logs) until attribute routing
rules (milestone 5) place them — configure rules before enabling such a
client. The SSO-manager grant list of a system-owned client is system-wide.
Re-parenting a client to a different owner (CLI-only, `saml:client update
<slug> --org=` / `--system=`) strands the old owner's grant list — the rows
aren't deleted, they just stop applying to this client, and the new owner
starts with an empty list of its own. For the same reason, retire a client by
disabling it (`saml:client disable <slug>`) rather than deleting its
`saml_clients` row by hand — there is no cascade, so a manually deleted row
strands its grants and routing rules as orphaned data instead of cleanly
removing them.

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

## Managing clients from the admin portal (SSOClients.php)

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

The API it consumes lives in this app at `/api/admin/saml-clients`. Both this
application and the admin portal's application need `ADMIN_API_TOKEN` set
(the same value in both `.env` files) for the portal to authenticate its API
calls. The CLI (`saml:client ...`) remains fully equivalent to the portal for
every operation the portal supports — use whichever is more convenient.

**Rollout note:** migrations run automatically when a login container starts
(the image entrypoint runs `php artisan migrate --force --isolated`). This is
safe against the shared database because the login app tracks its migration
history in its own `migrations_login` table — the shared `migrations` table
belongs to other applications and is never read or written. No manual
migration step is needed; a failed migration stops the new container before
it serves, leaving the previous deployment running.

## Attribute-based routing

Beyond the client's single static default department, a client can carry
**routing rules** that place (and, on repeat logins, move) users based on
what the customer's IdP actually asserts — department, role, group, or any
other attribute — instead of a fixed default. Rules read like Cloudflare page
rules: "if the assertion matches `[attribute] [operator] [value]`, then
place here." Manage them via `saml:client routing <slug>` (CLI —
list/`--set`/`--set-file`/`--clear`) or the "Routing rules" panel on a
client's edit dialog in the admin portal; both are backed by the same
validated write path (`SamlClientManager`); the portal reaches it through the
admin API.

There are two rule kinds, because they answer two different questions:

- **Organization rules** — "which organization does this user belong to?"
  Only meaningful on system-owned clients (an org-owned client's organization
  *is* its owner, so this stage is skipped for it). Each rule targets a
  specific `organization_id` from the client's owner scope. The portal only
  shows this section for system-owned clients.
- **Department rules** — "which department within that organization?" These
  target a **department name**, not a numeric ID. A hospital system with a
  dozen identically-structured organizations writes its department mappings
  *once* — a rule naming "ICU Nursing" resolves independently against
  whichever organization stage 1 picked, rather than needing one rule per
  org/department pair. If the named department doesn't exist in the resolved
  org, that's expected and not an error: evaluation just falls through to the
  next rule (or falls through to the client's static default, or the
  finish-account flow, if nothing resolves).

**Ordering and fall-through:** within each list, rules are evaluated in
order and the **first match wins** — with one refinement for department
rules: a department rule only "wins" if it both matches the assertion *and*
its named department actually resolves (exists and active) in the
organization stage 1 picked. A matching rule whose department doesn't exist
here yields to the next rule rather than stopping evaluation, which is what
makes shared rule sets safe to reuse across organizations that don't all
have the same departments.

**Operators** (a closed set, shared by both rule kinds): `equals`,
`not_equals`, `starts_with`, `not_starts_with`, `contains`, `not_contains`,
`ends_with`, `not_ends_with`, `wildcard`, `strict_wildcard`. Two things to
know before writing a rule:

- SAML attributes are multi-valued. The positive operators (`equals`,
  `contains`, …) match when **any** asserted value satisfies the comparison;
  an absent attribute never matches. The negated operators (`not_equals`,
  `not_contains`, …) match when **no** asserted value satisfies the positive
  form — which means they match vacuously when the attribute is absent
  entirely. Keep that in mind for a `not_equals` rule meant to exclude a
  specific group: it also matches everyone who was never asserted a group at
  all.
- `wildcard` and `strict_wildcard` are `*`-pattern matches (zero or more
  characters, anchored over the whole value). `wildcard` is
  case-insensitive, matching every other operator; `strict_wildcard` is the
  one case-sensitive operator in the set — reach for it only when a
  customer's IdP asserts values whose casing is meaningful.

**Catch-alls:** the reserved triple `attribute = *`, `operator = wildcard`,
`value = *` matches every login. It's the only legal use of `*` as an
attribute name, and it is only legal as the **last** rule in its list —
anything placed after a catch-all can never be reached and is rejected at
save time. An org-rule catch-all is how a system-owned client says
"everyone else belongs to org X"; a department-rule catch-all says
"everyone else lands in the department named Y, where it exists." The
portal's "match everyone" checkbox on the last row of a list fills in this
triple for you.

**Owner-scope confinement:** an org rule's `organization_id` must be one of
the client's `scopedOrganizationIds()` — the owning org itself for an
org-owned client, or a member organization for a system-owned one. Rules
that reach outside that scope are rejected, same as grants.

**The IdP is authoritative, every login.** Department rules aren't a
one-time placement — they're re-evaluated on every login. If a rule resolves
to a department different from the user's current one, the user moves
(including across organizations, if stage 1 says so). No-match logins never
demote or unplace anyone, but a resolvable match always wins: a department
an Apex admin sets by hand survives only until the next login where a
department rule resolves to something else. Don't hand-place a user into a
department a routing rule will contest.

**Entra/Okta attribute names:** write the `attribute` field exactly as the
IdP sends it. Okta sends short attribute names (`department`, `role`, …), so
those work directly. Entra emits URI-style claim names by default (e.g.
`http://schemas.xmlsoap.org/ws/2005/05/identity/claims/department`, or a
custom claim's full URI) — for an Entra client, the rule's `attribute` field
needs the full claim URI, not the short name, the same reminder as the
attribute map in Step 4 above.

### Known attributes

Writing a rule's `attribute` field from memory or a customer's IdP-config
screenshot is error-prone, so the app tracks, per client, every attribute
**name** it has actually seen asserted in a real login (excluding the
identity attributes already covered by the attribute map). These are the
client's **known attributes**, and they back two things in the portal's edit
dialog: a "Known attributes" strip showing each captured name (with when it
was last seen), and the routing-rule `attribute` field itself, which is a
strict dropdown populated from this list rather than free text — a rule can
only reference a name the client is known to have asserted (or one added
manually, below).

Only the attribute **name** is ever captured — never the value. Values are
PHI for some customers, so the collector reads assertion keys and discards
everything else; this is enforced at the point of capture, not by a filter
downstream. `saml:client routing`/the portal's routing panel are the only
consumers of this data.

Known attributes populate automatically the first time a client's users log
in — nothing to configure. To build a rule set *before* the first login (or
to reference an attribute the IdP asserts only intermittently, e.g. a group
claim that's empty for some users), add the name manually via the strip's
input; it behaves the same as a captured one for rule-building purposes.
Removing a known attribute from the strip only removes it from the dropdown
going forward — it does not touch any routing rule already saved against
that name, and the name reappears automatically the next time it's asserted.

Full assertion logging (recording more than names, for audit purposes) is a
separate, not-yet-built capability — do not rely on known attributes for
anything beyond populating the routing-rule dropdown.

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
| `no_employee_match`           | SAML login on an admin-portal client asserted an email with no active Employees row.             | Verify the employee's Email and Active='Y' in the Employees table; admin SSO never auto-creates accounts. |
| `disabled_user`               | The matched Apex user has `Disabled='Y'`.                                                       | Reactivate the account in the admin site if the user should regain access.                              |
| `unknown_user_jit_disabled`   | No existing Apex user matches the login, and JIT provisioning is off for this client.            | Either enable JIT (`saml:client update <slug> --jit`) or pre-create the user account manually before the customer's users start logging in. |
| `unrouted_user`               | New user on a system-owned client with no matching routing rule.                                 | Add routing rules (including a catch-all) or verify the IdP asserts the expected attributes. |

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
