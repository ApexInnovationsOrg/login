# SP-Initiated SSO and Frontend Modernization — Design

Milestone 3 of the SSO contract: service-provider-initiated login — the user enters an
email on the login screen and is routed to their organization's IdP or to the password
flow. Builds directly on the milestone-2 `saml_clients` model and ACS endpoint.

Folded into this milestone: the frontend modernization pass (Mix → Vite, Inertia client
0.9 → 2.x). The SSO work rewrites the login page, and writing it against the old stack
would mean writing it twice; the old stack also carries all of the current `npm audit`
findings (4 high — the runtime Inertia 0.9 client and its axios; 6 moderate — webpack-era
dev tooling). Arguably milestone-1 work that was deferred; it lands here because
milestone 3 needs it.

## Login UX

Two-step, email-first:

1. The login page shows only an email field and a Continue button.
2. Continue asks the server whether the email's domain belongs to an SSO client.
   - Match → the browser navigates to the SP-initiated login endpoint, which redirects
     to the customer's IdP.
   - No match → the password field and Log in button appear on the same page; the email
     field never unmounts (keeps password-manager autofill working). The forgot-password
     link lives on this step.

SSO is **enforced** for matched domains: no password fallback in the UI, and the password
login endpoint independently rejects those emails (see Enforcement).

## Data model

One migration: `email_domains` json column on `saml_clients`, default `[]`. Domains are
stored lowercased, without `@`.

`SamlClientManager` validates on create/update: each entry is a well-formed hostname, and
no other client (enabled or not) already claims it. Uniqueness is enforced in code — a
json column cannot carry a DB unique index, and at this scale (a handful of domains per
client, single write path through the manager) that is sufficient.

`SamlClient::forEmailDomain(string $domain): ?SamlClient` returns the **enabled** client
claiming the domain.

Rejected alternative: a separate `saml_client_domains` table. Proper relational shape and
DB-level uniqueness, but adds a migration, model, and CLI surface for data measured in
rows-per-client of single digits. Milestone 4's admin tools can normalize later if needed.

## CLI

- `saml:client create` / `update` gain `--domains=a.com,b.com`. Update **replaces** the
  whole list — merge semantics are a trap.
- The create wizard prompts for domains (comma-separated, blank to skip).
- `list` and `describe` display each client's domains.

## Lookup endpoint

`POST /sso/lookup`, guest middleware, `throttle` middleware (per-IP, on the order of the
login limiter's 5/minute). Body: `{email}`.

Always responds 200 with `{"sso": string|null}`:

- Domain matches an enabled client → `"/saml/{slug}/login"`.
- Anything else — unknown domain, disabled client, malformed or missing email —
  → `null`, never a validation error.

Unknown-domain and known-org-without-SSO responses are byte-identical so the endpoint
cannot be used to enumerate which organizations are Apex SSO customers.

## SP-initiated login endpoint

`GET /saml/{slug}/login` on `SamlController`:

- Resolve the enabled client by slug; 404 otherwise (same contract as the ACS).
- Build the php-saml `Auth` from the existing `SamlSettingsFactory` (`idp_sso_url` was
  captured in milestone 2 for exactly this).
- `$auth->login(returnTo: null, stay: true)` and issue the 302 ourselves — `stay: true`
  keeps php-saml from calling `header()` directly.

No RelayState: the post-login destination is always MyCurriculum (or the finish-account
flow), identical to IdP-initiated. No AuthnRequest-ID persistence: the ACS accepts
unsolicited assertions by design (IdP-initiated is a supported flow), so tracking
`InResponseTo` would add state without adding a guarantee.

## Enforcement

`AuthenticatedSessionController::store` rejects password attempts for any email whose
domain maps to an enabled SSO client, with a validation error directing the user to SSO.
Without this, posting to `/login` directly would bypass the two-step routing entirely.

## Frontend modernization

Done first, as its own commits, before the login page is touched:

- **Mix → Vite** via `laravel-vite-plugin`: `vite.config.js`, `@vite` directive in the
  blade root template, `VITE_`-prefixed env vars. `laravel-mix`, `webpack-*`, and their
  vulnerable dev-server family leave `package.json`.
- **Inertia client 0.9 → `@inertiajs/vue3` 2.x** (one package; progress indicator is
  built in, so `@inertiajs/inertia`, `@inertiajs/inertia-vue3`, and `@inertiajs/progress`
  are all removed). The server side is already `inertia-laravel` 2 (milestone 1) and
  verified protocol-compatible with the old client, so this is a client-only move.
- **axios** becomes a direct, current dependency (the vulnerable copy was transitive via
  Inertia 0.9).
- Existing pages get the mechanical updates the new client requires; no rewrites beyond
  that. PrimeVue, Tailwind, and Ziggy stay at current versions — nothing beyond what the
  migration forces.
- `package-lock.json` is committed (none exists today; fresh installs currently resolve
  a broken laravel-mix/webpack combination).

Exit criteria: `npm audit` reports none of the current 4 high / 6 moderate findings, and
every auth page renders and submits in the E2E environment.

## Asset build and deploy

`public/build/` is gitignored; the committed `public/js/`, `public/css/`, and
`mix-manifest.json` come out of the repo. The image builds gain a node stage running
`npm ci && npm run build`:

- Built assets → the **nginx** image.
- `public/build/manifest.json` → the **php-fpm** image (Laravel resolves asset URLs from
  the manifest server-side; this mirrors the current mix-manifest split).
- Both the dev and master workflows build the node stage — dev deploys ship frontend
  changes for the first time (today only master builds the nginx image).

Local/E2E: `npm run build` inside the login container, or the Vite dev server while
iterating.

## Branch strategy

All of this lands on one branch off `milestone2-saml` (the milestone-2 PR is still in
review; the SSO half builds on its code, and waiting would block the milestone). Riskier
than stacking separate PRs — a frontend regression and the SSO feature now share review
fate — accepted deliberately to keep moving. Commit order inside the branch stays
disciplined: modernization first, then SSO, so the two are separable in review and in
`git bisect`.

## Testing

- **Lookup**: match, no-match, disabled client, case-insensitivity, malformed email,
  throttling, and response-shape equality between the no-match variants.
- **SP login endpoint**: 302 to the client's `idp_sso_url` carrying a valid deflated
  `SAMLRequest`; 404 for unknown/disabled slugs.
- **Enforcement**: password login rejected for SSO-domain emails.
- **CLI**: domain validation, cross-client uniqueness, list replacement on update,
  wizard prompt.
- **E2E**: extend the mock-IdP script with the SP-initiated round trip (login page →
  lookup → SP login → mock IdP → ACS → session). The milestone-2 signed-response builder
  already covers the ACS side.
- **Modernization smoke**: every auth page (login, forgot/reset password, finish user,
  admin reset, confirm password) exercised in the E2E environment after the Vite/Inertia
  switch, before SSO work begins.

## Out of scope

- Attribute-based routing (milestone 5) and admin UI for domains (milestone 4).
- RelayState / deep-link return URLs.
- Per-client "allow password too" flag — SSO is all-or-nothing per domain; revisit only
  if a customer asks.
- PrimeVue/Tailwind major upgrades.
