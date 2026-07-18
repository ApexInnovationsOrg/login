# Hierarchy Factory Tooling — Design

Dev tooling for generating a full System → Organizations → Departments tree in
one expression, usable from both feature tests and local dev seeding. The
building blocks already exist (`SystemFactory`, `OrganizationFactory`,
`DepartmentFactory`, and the `SystemOrganizations` join), but composing them is
verbose — `tests/Support/SeedsSystemHierarchy` hand-inserts join rows — and
there is no local-dev entry point at all.

Two facts shaped the design:

- **System↔Organization is stored many-to-many but is semantically one-to-many.**
  `SystemOrganizations` (own `ID` PK, `SystemID`, `OrganizationID`, no unique
  index on `OrganizationID`) can hold multiple systems per org, but the business
  rule is that an organization belongs to at most one system. Nothing in the DB
  enforces this, so the tooling must — and the org-side relation should read as
  singular.
- **`Organization::systems()` has no consumers.** It is defined but never
  called, so replacing it with the singular truth is a free correction, not a
  migration.

## Scope decisions (settled during design)

- `forSystem()` accepts a `System` model, a string, or nothing. A string is a
  lookup-or-create by `Name`, so repeated calls with the same name share one
  system.
- `withDepartments()` accepts an int (N distinct names from the realistic pool)
  or an array of exact names (for tests that assert on a specific department).
- A system-side, whole-tree entry point exists too:
  `System::factory()->withOrganizations(3, departmentsEach: 4)`.
- Local dev seeding follows the existing `Local*` seeder pattern
  (`LocalHierarchySeeder`), not an artisan command.
- Error handling stays minimal: this is dev/test-only code, so bad inputs (e.g.
  a department name colliding with the per-org unique index) surface as natural
  DB errors.

## Data model corrections

- `App\Models\SystemOrganization` becomes a real pivot: `extends Pivot`,
  `$table = 'SystemOrganizations'`, `$primaryKey = 'ID'`, `$incrementing =
  true`, `$timestamps = false`, fillable `SystemID`/`OrganizationID`.
- `Organization::systems()` (belongsToMany, unused) is replaced by
  `Organization::system()`, a
  `hasOneThrough(System::class, SystemOrganization::class)` keyed
  `OrganizationID`/`SystemID`, so `$org->system` reads as the single owning
  system.
- `System::organizations()` stays `belongsToMany` (a system genuinely has many
  orgs) and gains `->using(SystemOrganization::class)`.

## OrganizationFactory sugar

`forSystem(System|string|null $system = null): static`

Resolution is deferred to `afterCreating` so lookups happen at create time, not
state-definition time:

- `System` model → attach to it directly.
- string → `System::where('Name', $name)->first() ?? System::factory()
  ->create(['Name' => $name])`.
- no argument → a fresh `System::factory()->create()`.

The attach is `SystemOrganization::updateOrCreate(['OrganizationID' =>
$org->ID], ['SystemID' => $system->ID])` — keyed on the org, which *enforces*
the one-system rule: re-attaching replaces the row rather than adding a second
system.

`withDepartments(int|array $departments = 3): static`

- int → `has(Department::factory()->count($n), 'departments')` with a shuffled
  per-org `Sequence` over `DepartmentFactory::NAMES`. This deliberately
  bypasses faker's global `unique()`, so seeding many orgs cannot exhaust the
  24-name pool (names are unique per org, which is all the legacy
  `UNIQUE(Name, OrganizationID)` index requires).
- array → the same, sequenced over the exact names given.

## SystemFactory sugar

`withOrganizations(int $count = 2, int|array $departmentsEach = 3): static`

Composes the above:
`has(Organization::factory()->count($count)->withDepartments($departmentsEach),
'organizations')`. One call builds the whole tree:

```php
System::factory()->withOrganizations(3, departmentsEach: 4)
    ->create(['Name' => 'Memorial Health System']);
```

The belongsToMany `has()` writes the join rows through the pivot; orgs created
this way get exactly one system by construction.

## LocalHierarchySeeder

Follows the `Local*` seeder pattern: creates one named system
("Memorial Health System") with a few orgs of 3–4 departments each, plus one
standalone org (no system) with departments — the no-system shape exists in
production data and must stay exercised locally. Guarded by a name check so
re-running does not duplicate. Run inside the Sail container:

```bash
php artisan db:seed --class=LocalHierarchySeeder
```

## Testing

New cases in `tests/Feature/HierarchyFactoryTest`:

- `forSystem('Same Name')` on two orgs shares a single system (one `Systems`
  row, two join rows).
- `forSystem($model)` attaches to the given system; `forSystem()` with no
  argument creates a fresh one.
- Re-attaching an org to a different system replaces the join row (one row per
  org, hasOne enforced).
- `$org->system` (hasOneThrough) returns the attached system.
- `withDepartments(4)` creates four departments with distinct names;
  `withDepartments(['Emergency', 'ICU'])` creates exactly those.
- `withOrganizations(3, departmentsEach: 2)` builds the full tree: three orgs
  each attached to the system, two departments each; also with an array of
  names applied per org.
- A many-org seed (e.g. 10 orgs × 3 departments) does not exhaust the name
  pool.

`SeedsSystemHierarchy` shrinks to the new sugar internally; its call sites are
untouched.

## Rollout

Test/dev-only surface: no migrations, no production code paths touched beyond
the unused `Organization::systems()` → `system()` relation swap and the inert
pivot registration on `System::organizations()`. Stacks on
`milestone5-routing`.
