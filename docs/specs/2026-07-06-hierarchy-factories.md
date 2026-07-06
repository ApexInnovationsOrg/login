# Hierarchy Factories — Design

First-class factories and relationships for the `System → Organization → Department → User`
hierarchy, plus helpers to grant the three admin tiers, so tests and the `local:users`
command build the hierarchy through Eloquent instead of hand-written `DB::table()` inserts
that must spell out every legacy NOT-NULL column.

Motivation: the recent SAML wizard tests had to seed `Organizations`/`Departments` with full
scalar-column lists and `$seed = true` (for the `CountryID` foreign key) because no factories
exist. Only `Organization` and `Department` models exist today (both carry `HasFactory` but no
factory); `System` and the admin-membership tables are touched only via raw `DB::table()` in
`CreateLocalUsers`.

## The real hierarchy

```
Systems
  └─ SystemOrganizations (join: SystemID, OrganizationID)
       └─ Organizations
            └─ Departments (OrganizationID)
                 └─ Users (DepartmentID)

Admin tiers are NOT columns on Users — they are membership rows:
  SystemAdmins       (SystemID, UserID)
  OrganizationAdmins (OrganizationID, UserID, + defaulted preference columns)
  DepartmentAdmins   (DepartmentID, UserID, + defaulted preference columns; UNIQUE(DepartmentID, UserID))
```

All these are legacy tables: PascalCase columns, integer PKs named `ID`, `$timestamps = false`.

## Prerequisite: a `LegacyModel` base for the shared-schema convention

The root cause of the seeding pain is that every model hand-rolls its conventions against the
legacy schema, and most get the primary key wrong. Of the nine models, only `User` declares
`protected $primaryKey = 'ID'`; the other eight legacy-schema models
(`Organization`, `Department`, `Credential`, `ProfessionalRole`, `States`,
`CredentialLicenseTypes`, `ProfessionalCredentialFilters`, and the new `System`) inherit
Laravel's default `'id'` while the tables' actual primary key is `ID`. So `->id`, `->find()`,
and every primary-key-based relationship silently resolve the wrong column (verified:
`Organization::getKeyName()` returns `'id'`, `$org->id` is `null`).

Introduce **`app/Models/LegacyModel.php`** — an abstract base extending `Model` that encodes the
one convention that is genuinely universal across these legacy tables:

```php
abstract class LegacyModel extends Model
{
    protected $primaryKey = 'ID';   // every legacy table's PK is an unsigned-int `ID`
    // $incrementing = true and int key type are the correct defaults; declared explicitly for clarity.
}
```

The seven plain-`Model` legacy models extend `LegacyModel` (instead of `Model`) and drop their
own `$primaryKey` line. `User` is the exception: it must extend `Authenticatable` (auth
requirement), so it keeps its own `protected $primaryKey = 'ID';` — which it already had. This
fixes the silent PK bug across the board and stops the next legacy model from reintroducing it.

**Timestamps stay per-model, NOT in the base.** Two of these tables — `ProfessionalRoles` and
`ProfessionalCredentialFilters` — actually have `created_at`/`updated_at` columns and their
models correctly leave timestamps on; the rest have no such columns and set
`public $timestamps = false;`. Baking `$timestamps = false` into the base would break the two
timestamped tables, so each model keeps its own timestamps declaration.

**`SamlClient` is deliberately excluded.** It is app-owned (created in Milestone 2 with
`$table->id()` and `$table->timestamps()`), so it correctly uses stock Laravel conventions and
continues to extend `Model`, not `LegacyModel`. New app-owned tables follow the same rule.

Fixing the primary key also makes passing an explicit `['ID' => 933]` through a factory behave
correctly (the seeder relies on this — see "Rebuilding ReferenceDataSeeder").

## Models and relationships

- **`System`** — new model extending `LegacyModel`, table `Systems`, `$timestamps = false`,
  `HasFactory` (inherits `$primaryKey = 'ID'` from the base).
  - `organizations(): BelongsToMany` — to `Organization` through `SystemOrganizations`
    (`SystemID` / `OrganizationID`).
- **`Organization`** (existing) — now extends `LegacyModel`; add:
  - `departments(): HasMany` — to `Department` on `OrganizationID`.
  - `systems(): BelongsToMany` — to `System` through `SystemOrganizations`.
- **`Department`** (existing) — now extends `LegacyModel`; keep the existing `org()` relation
  untouched (may be in use); add:
  - `organization(): BelongsTo` — conventionally named, on `OrganizationID`.
  - `users(): HasMany` — to `User` on `DepartmentID`.
- **`User`** (existing) — add:
  - `department(): BelongsTo` — on `DepartmentID`.
  - admin-scope query relations: `adminDepartments()`, `adminOrganizations()`, `adminSystems()`
    (`BelongsToMany` through the three admin tables).
  - admin-grant helpers (see below).

The three admin tables are represented by relationships and helper methods, not by their own
models/factories — the rows are pure join records and a full model per join table adds surface
without value.

## Factories

Each factory fills the legacy tables' required NOT-NULL columns so a bare
`Factory::new()->create()` succeeds against the real constraints.

- **`SystemFactory`** — `Name` (unique: faker company + a unique suffix to satisfy the
  `UNIQUE KEY Name`), `CreationDate` = today. Organizations are attached through the
  relationship, e.g. `System::factory()->has(Organization::factory()->count(2))->create()`,
  which writes the `SystemOrganizations` join rows.
- **`OrganizationFactory`** — every required scalar column:
  `Name`, `Address`, `City`, `PostalCode`, `Phone`, `CreationDate`, `CountryID` = 231,
  `PasswordMinLength` = 6, and `PasswordComplexityNumeric/Special/Uppercase/Lowercase` = 'N'.
  `StateID` is nullable (its FK only fires when set), so the factory leaves it null and does not
  touch `States`. `CountryID` is NOT NULL with a foreign key to `Countries`, so before building
  the `definition()` calls a private helper that runs
  `DB::table('Countries')->updateOrInsert(['ID' => 231], ['Abbreviation' => 'US', 'Name' => 'United States'])`,
  guaranteeing the `CountryID` foreign key resolves even when no seeder has run. No new
  `Country` model is introduced.
- **`DepartmentFactory`** — `Name`, `Active` = 'Y', and `OrganizationID` from a nested
  `Organization::factory()` by default so `Department::factory()->create()` brings its own org;
  overridable with `->for($organization)`.

Factory states for the seeder's fixed rows (so the seeder builds through the factory rather than
duplicating column lists):

- `OrganizationFactory::strict()` — the strict-password preset
  (`PasswordMinLength` = 12, all four `PasswordComplexity*` = 'Y'), used by organization 2.
  The default definition is already the permissive preset the other orgs use.
- **`UserFactory`** (update) — replace the hardcoded `'DepartmentID' => 1` with a nested
  `Department::factory()` so a bare `User::factory()->create()` builds a full
  department → organization chain. The nested default only applies when no `DepartmentID` is
  passed, so existing tests that pass an explicit `DepartmentID` are unaffected. The existing
  `unfinished()`, `disabled()`, and `adminReset()` states stay as-is.

## Admin-grant helpers on `User`

Membership is granted idempotently (honoring the tables' unique keys) and returns the user for
chaining:

```php
$user->makeDepartmentAdmin(Department $department): static
$user->makeOrganizationAdmin(Organization $organization): static
$user->makeSystemAdmin(System $system): static
```

Each inserts a row into the corresponding admin table only if an equivalent row does not already
exist (the preference columns rely on their schema defaults). Tests read naturally:
`$user->makeOrganizationAdmin($org)`; `CreateLocalUsers` reuses the same helpers.

## Rebuilding `ReferenceDataSeeder` on the factories

`ReferenceDataSeeder` seeds fixed, well-known IDs that application code and tests hardcode
(organization 933 for the SAML listener; orgs 1/2 and their departments for password-rule
behavior; state 66; professional role 3). It is rebuilt to run through the same factories, so the
whole dev/test setup uses one consistent construction path — the factory defines *how* a row is
built; the seeder pins *which* fixed identity it gets.

- **Organizations** — replace the three `DB::table('Organizations')->insert([...])` literals with
  factory calls that pass the explicit `ID` and `Name` (and the `strict()` state for org 2):

  ```php
  Organization::factory()->create(['ID' => 1, 'Name' => 'Local Dev Organization']);
  Organization::factory()->strict()->create(['ID' => 2, 'Name' => 'Strict Password Organization']);
  Organization::factory()->create(['ID' => 933, 'Name' => 'SSO Organization']);
  ```

  Passing `['ID' => n]` works because the models now declare the correct primary key and these
  PKs auto-increment (an explicit value is honored on insert). The factory supplies every other
  required column, so the password-complexity fields no longer need to be spelled out except via
  the `strict()` state.

- **Departments** — replace with `Department::factory()->for($org)->create(['ID' => n, 'Name' => ...])`
  (or pass `'OrganizationID'` explicitly) for the three fixed departments (1 → org 1, 2 → org 2,
  3 → org 933).

- **`Countries` (231) and `States` (1, 2, 66)** — these are plain lookup rows with no factory
  in scope. `OrganizationFactory` already guarantees `Countries` 231 via `updateOrInsert`; the
  seeder keeps its explicit `Countries`/`States` inserts (idempotent) since those lookup tables
  are not part of the hierarchy being modeled. Non-hierarchy reference rows the seeder also
  inserts (`Credentials`, `ProfessionalRoles`, `ProfessionalCredentialFilters`,
  `CredentialLicenseTypes`) likewise stay as explicit inserts — they are outside this hierarchy
  and have no factories.

The seeder stays idempotent overall (it runs on a fresh `migrate:fresh` database), and the fixed
IDs/names are unchanged, so everything that depends on org 933 / 1 / 2 and departments 1 / 2 / 3
continues to resolve.

## Refactor `CreateLocalUsers`

`app/Console/Commands/CreateLocalUsers.php` currently reimplements the entire hierarchy with raw
`DB::table()` inserts and `insertGetId`. Rewrite it to use the new factories and admin helpers,
producing the **same** resulting hierarchy and the **same** printed output (the login/role table
and the org/department/user-count table). This removes the duplication and is the proof the
factories model the real shape.

Constraints preserved: the command stays dev-only (`app()->environment('production')` guard) and
idempotent (re-running does not duplicate rows) — the fixed, well-known orgs/departments it
references (1/2/933, and system membership boundaries) continue to come from `ReferenceDataSeeder`;
the command adds the demo *departments and users* on top, now via factories.

## Out of scope

- Non-hierarchy reference rows in `ReferenceDataSeeder` (`Countries`, `States`, `Credentials`,
  `ProfessionalRoles`, `ProfessionalCredentialFilters`, `CredentialLicenseTypes`) keep their
  explicit inserts — they are lookup tables outside the `System → Organization → Department → User`
  hierarchy and get no factories.
- No new models for the admin join tables (relationships + helpers cover the need).
- No change to the SAML client model/factory or the wizard behavior (though the wizard/lookup
  tests' seeding is simplified to use the new factories — see Testing).

## Testing

- **`tests/Feature/HierarchyFactoryTest.php`** (new):
  - `Organization::factory()->create()` succeeds with all NOT-NULL/FK constraints satisfied,
    with no seeder run.
  - `Department::factory()->create()` creates and links its own organization.
  - `System::factory()->has(Organization::factory()->count(2))->create()` creates two
    `SystemOrganizations` join rows; `$system->organizations` returns both.
  - `User::factory()->create()` builds a full department → organization chain (assert
    `$user->department->organization` resolves).
  - each `make*Admin` helper inserts exactly one membership row, is idempotent (calling twice
    does not duplicate), and the matching `admin*` relation returns the attached scope.
  - `Organization::factory()->create(['ID' => 933])` persists with `ID` 933 and
    `$org->id === 933` (proves the primary-key fix), and `OrganizationFactory::strict()` produces
    the 12-char / all-complexity preset.
- **`LegacyModel` migration:** a test asserting every migrated legacy model reports
  `getKeyName() === 'ID'` (parameterized over the eight models), and that the two timestamped
  models (`ProfessionalRole`, `ProfessionalCredentialFilters`) still have `$timestamps === true`
  while the rest report `false` — i.e. moving to the base did not silently flip timestamp
  behavior. `SamlClient::getKeyName()` stays `'id'` (unaffected).
- **Seeder regression:** after `migrate:fresh --seed`, assert the fixed fixtures still exist with
  their exact IDs and names (organization 933 = "SSO Organization", org 2 is the strict preset,
  departments 1/2/3 mapped to the right orgs) — i.e. the factory-based rebuild produced the same
  fixed data the raw inserts did.
- **Simplify existing tests:** update `SamlClientWizardLookupTest` and `SamlClientWizardTest` to
  seed organizations/departments via `Organization::factory()` / `Department::factory()` instead
  of the verbose `DB::table()->insert()` column lists (dogfoods the factories; keeps their
  assertions unchanged).
- **Regression:** the full suite stays green; `make db` → `local:users` still produces the same
  hierarchy and output.
