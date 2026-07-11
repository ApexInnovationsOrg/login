<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Organization;
use App\Models\System;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLocalUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'local:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a local user hierarchy (system -> orgs -> departments -> users) with one login per admin tier. Local development only.';

    /**
     * The hierarchy this creates:
     *
     *   Local Health System
     *     Local Dev Organization (1)
     *       Emergency   <- department.admin@ administers, 5 users
     *       Cardiology  <- 4 users
     *       Nursing     <- 6 users
     *     SSO Organization (933)
     *       Radiology   <- 3 users
     *   (outside the system)
     *     Strict Password Organization (2)
     *       Compliance  <- 3 users   [system admin should NOT see these]
     *
     * Admin roles are not columns on Users — membership rows in the
     * DepartmentAdmins / OrganizationAdmins / SystemAdmins tables make a
     * user an admin (see the nav query in website_root/inc/navigation.html).
     *
     * @return int
     */
    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('Local development only.');

            return 1;
        }

        // Orgs 1/2/933 and departments come from ReferenceDataSeeder; this command
        // adds the demo departments/users/admins on top, idempotently.
        $system = System::firstOrCreate(
            ['Name' => 'Local Health System'],
            ['CreationDate' => now()->format('Y-m-d')],
        );
        $this->membership('SystemOrganizations', ['SystemID' => $system->ID, 'OrganizationID' => 1]);
        $this->membership('SystemOrganizations', ['SystemID' => $system->ID, 'OrganizationID' => 933]);
        // Organization 2 stays OUTSIDE the system, so system-admin scoping has a boundary.

        $departments = [
            'Emergency' => ['org' => 1, 'users' => 5],
            'Cardiology' => ['org' => 1, 'users' => 4],
            'Nursing' => ['org' => 1, 'users' => 6],
            'Radiology' => ['org' => 933, 'users' => 3],
            'Compliance' => ['org' => 2, 'users' => 3],
        ];

        $deptIds = [];
        foreach ($departments as $name => $spec) {
            $deptIds[$name] = $this->department($name, $spec['org']);
            $this->fillDepartment($deptIds[$name], $name, $spec['users']);
        }

        $rows = [];

        $this->user('department.user@example.test', 'Regular', 'User', $deptIds['Emergency']);
        $rows[] = ['department.user@example.test', 'Regular user in Emergency (Org 1)'];

        $user = $this->user('department.admin@example.test', 'Department', 'Admin', $deptIds['Emergency']);
        $user->makeDepartmentAdmin(Department::find($deptIds['Emergency']));
        $rows[] = ['department.admin@example.test', 'Department admin of Emergency (5 users)'];

        $user = $this->user('organization.admin@example.test', 'Organization', 'Admin', $deptIds['Nursing']);
        $user->makeOrganizationAdmin(Organization::find(1));
        $rows[] = ['organization.admin@example.test', 'Org admin of Org 1 (Emergency, Cardiology, Nursing)'];

        $user = $this->user('system.admin@example.test', 'System', 'Admin', $deptIds['Nursing']);
        $user->makeSystemAdmin($system);
        $rows[] = ['system.admin@example.test', 'System admin (Orgs 1 + 933, NOT Org 2)'];

        $this->table(['Login (password: "password")', 'Role'], $rows);

        $counts = DB::table('Departments as D')
            ->join('Organizations as O', 'O.ID', '=', 'D.OrganizationID')
            ->leftJoin('Users as U', 'U.DepartmentID', '=', 'D.ID')
            ->groupBy('O.Name', 'D.Name')
            ->orderBy('O.Name')
            ->select('O.Name as Org', 'D.Name as Department', DB::raw('COUNT(U.ID) as Users'))
            ->get()
            ->map(fn ($r) => [(string) $r->Org, (string) $r->Department, (string) $r->Users]);

        $this->table(['Organization', 'Department', 'Users'], $counts->all());

        return 0;
    }

    private function department(string $name, int $orgId): int
    {
        $existing = Department::where(['Name' => $name, 'OrganizationID' => $orgId])->first();
        if ($existing) {
            return $existing->ID;
        }

        return Department::factory()->create([
            'Name' => $name,
            'OrganizationID' => $orgId,
        ])->ID;
    }

    private function fillDepartment(int $deptId, string $deptName, int $count): void
    {
        $slug = Str::lower($deptName);
        for ($i = 1; $i <= $count; $i++) {
            $this->user("$slug.user$i@example.test", ucfirst($slug), "User$i", $deptId);
        }
    }

    private function user(string $login, string $first, string $last, int $deptId): User
    {
        $user = User::where('Login', $login)->first();
        if ($user) {
            if ($user->DepartmentID != $deptId) {
                $user->DepartmentID = $deptId;
                $user->save();
            }

            return $user;
        }

        return User::factory()->create([
            'Login' => $login,
            'FirstName' => $first,
            'LastName' => $last,
            'DepartmentID' => $deptId,
        ]);
    }

    private function membership(string $table, array $keys): void
    {
        if (! DB::table($table)->where($keys)->exists()) {
            DB::table($table)->insert($keys);
        }
    }
}
