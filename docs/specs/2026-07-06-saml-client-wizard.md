# `saml:client create --wizard` — Design

An interactive onboarding flow for the `saml:client create` artisan command so operators
select an organization and department **by name** instead of recalling numeric IDs, and
can set a non-default attribute map (needed for Entra/Azure) without a manual database edit.

This is a follow-on to the Milestone 2 SAML work (`docs/specs/2026-07-05-saml-idp-initiated-login.md`);
it adds no new SAML behavior, only an ergonomic front door to the existing client-creation path.

## Approach

Add a `--wizard` flag to the existing `create` action. When present, the command runs an
interactive prompt sequence that gathers the same fields the flags provide, then calls the
unchanged `SamlClientManager::create()`. All existing validation (unique slug, required org,
`alpha_dash` slug) still runs; validation errors surface exactly as they do for the
flag-based path. Plain flag-based `create --name ... --org ...` is untouched, so existing
usage and any scripts keep working.

Uses `laravel/prompts` (already vendored with Laravel 12 — no new dependency). Its global
helpers `text()`, `search()`, `select()`, and `confirm()` provide the prompts; `search()`
takes a closure so the organization/department lookups query the legacy tables as the
operator types.

## Interactive flow

`php artisan saml:client create --wizard` runs these steps in order:

1. **Name** — `text()`, required. The slug auto-derives via `Str::slug($name)`. A second
   optional `text()` is pre-filled with the derived slug so the operator can accept it with
   enter or override it.
2. **Organization** — `search()` querying `Organizations` (`ID`, `Name`) filtered by the
   operator's typed input; the option list shows names and the selection resolves to the
   organization's `ID`. The legacy database holds hundreds of organizations, so
   search-as-you-type is required rather than a flat select.
3. **Department** — the chosen organization's **active** departments (`Departments` where
   `OrganizationID = <chosen org>` and `Active = 'Y'`), presented with an explicit
   **"None — users choose at finish-account"** entry at the top that maps to `null`. Use
   `search()` (search-as-you-type, same primitive as the organization step) so the step
   behaves consistently and scales to organizations with many departments; the "None" entry
   is always included in the option list regardless of the search term. Choosing "None" is the
   normal case for an Okta customer (users complete their profile via the finish-account flow);
   making it a visible first option keeps that a deliberate choice.
4. **JIT provisioning** — `confirm('Auto-create unknown users on first login?')`, default
   yes (the Okta norm).
5. **Attribute names** — `confirm('Customize attribute names? (needed for Entra/Azure)')`,
   default no. If no, the Okta defaults apply (`email` → `email`, `first_name` → `firstName`,
   `last_name` → `lastName`). If yes, three `text()` prompts pre-filled with those defaults
   collect the `email`, `first_name`, and `last_name` claim names, which become the client's
   `attribute_map`.

After the prompts, the command shows a confirmation summary (name, slug, organization **name**,
department **name or "none"**, JIT on/off) and then creates the client.

## Output

Identical to the current flag-based `create`: the disabled client is created, and the command
prints the operator-facing handoff block —

```
Created <Name> (<slug>). Give the customer:
  ACS URL:      <APP_URL>/saml/<slug>/acs
  Metadata URL: <APP_URL>/saml/<slug>/metadata
  Entity ID:    <saml.sp.entity_id>
Then: saml:client update <slug> --metadata=<their-metadata.xml> && saml:client enable <slug>
```

The wizard is **create-only**. The onboarding gap while the customer configures their IdP and
sends metadata is a human wait; the operator resumes with `update --metadata` / `enable` (or a
future wizard extension) once the customer responds.

## Components

| Unit | Responsibility | Depends on |
|---|---|---|
| `SamlClientCommand` `--wizard` option + `runWizard(): array` | drive the prompt sequence, return the same input-array shape `createClient()` builds from flags | `laravel/prompts`, the two lookup helpers |
| `wizardOrganizationOptions(string $search): array` (command-private) | read-only `Organizations` lookup: `ID => Name`, filtered by search term, capped for scroll | `Organization` model |
| `wizardDepartmentOptions(int $orgId, string $search): array` (command-private) | read-only active-`Departments` lookup for the chosen org, with a leading "None" option (`null` value) | `Department` model |
| `SamlClientManager::create()` (unchanged) | validate and persist the client row | — |

The two lookup helpers are command-private methods rather than a separate class: they are two
read-only queries used only by the wizard, and the manager already owns all client-mutation
logic. Keeping them local avoids a new class for two `select`/`pluck` calls.

## Data flow

`runWizard()` returns an array shaped exactly like the flag-derived input the existing
`createClient()` already assembles:

```
[
  'name' => string,
  'slug' => string,              // derived or operator-overridden
  'organization_id' => int,      // resolved from the org search selection
  'department_id' => int|null,   // null when "None" chosen
  'jit_enabled' => bool,
  'attribute_map' => array|null, // present only when the operator customized it
]
```

`createClient()` then flows this through `SamlClientManager::create()` unchanged. The
`attribute_map` key is included only when customized; otherwise the manager applies its
`DEFAULT_ATTRIBUTE_MAP`.

## Error handling

- **Manager validation** (`ValidationException`, e.g. a slug that collides with an existing
  client) is already caught by `SamlClientCommand::handle()`, which prints each message and
  returns `FAILURE`. The wizard path inherits this unchanged — a collision after the prompts
  prints the error and exits non-zero; the operator re-runs.
- **Prompt-level validation** via `validate:` closures catches empty required fields (name)
  before submission, so the manager only sees structurally-complete input.
- **Empty org/department search results** (operator's term matches nothing) show the prompt's
  standard "no results" state; the operator refines the term. Departments for an org with no
  active departments still show the "None" option, so the step is always completable.

## Non-interactive environments

`laravel/prompts` degrades to plain line-by-line prompts when no interactive TTY is attached,
but the search UX assumes a real terminal. The command is run interactively via
`docker compose exec login php artisan saml:client create --wizard`, so this is expected. The
`--wizard` flag is not intended for non-interactive/CI use; the flag-based `create` remains the
scriptable path.

## Testing

- **Feature test** using `laravel/prompts`' `Prompt::fake()` with scripted keystrokes:
  - happy path — fake org and department search selections, JIT yes, default attributes →
    assert the created `saml_clients` row has the expected `organization_id`, `department_id`,
    `jit_enabled`, and the default `attribute_map`.
  - "None" department path → assert the row stores `department_id = null`.
  - customized attributes path → assert the row's `attribute_map` holds the operator-entered
    claim names.
  - slug override → assert a non-default slug is honored.
- **Regression test** — flag-based `create --name ... --org ...` (no `--wizard`) still creates a
  client with the same behavior as before, unchanged.
- **Lookup helpers** — seed a couple of organizations and departments (including an inactive
  department and a department under a different org) and assert `wizardDepartmentOptions` returns
  only the chosen org's active departments plus the leading "None" entry.

## Out of scope

- Extending the wizard through `update --metadata` / `enable` (the create-only decision above).
- A wizard for the `update`, `enable`, or `disable` actions.
- Any change to the flag-based command surface or to `SamlClientManager`'s validation rules.
- Non-interactive/scripted use of `--wizard`.
