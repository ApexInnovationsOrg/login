<?php

namespace App\Console\Commands;

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

        // A system groups organizations (Systems <- SystemOrganizations -> Organizations)
        $systemId = DB::table('Systems')->where('Name', 'Local Health System')->value('ID');
        if (! $systemId) {
            $systemId = DB::table('Systems')->insertGetId([
                'Name' => 'Local Health System',
                'CreationDate' => now()->format('Y-m-d H:i:s'),
            ]);
        }
        $this->membership('SystemOrganizations', ['SystemID' => $systemId, 'OrganizationID' => 1]);
        $this->membership('SystemOrganizations', ['SystemID' => $systemId, 'OrganizationID' => 933]);
        // Organization 2 deliberately stays OUTSIDE the system, so system-admin
        // scoping has a boundary to test against.

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

        $this->user('department.user@example.com', 'Regular', 'User', $deptIds['Emergency']);
        $rows[] = ['department.user@example.com', 'Regular user in Emergency (Org 1)'];

        $user = $this->user('department.admin@example.com', 'Department', 'Admin', $deptIds['Emergency']);
        $this->membership('DepartmentAdmins', ['DepartmentID' => $deptIds['Emergency'], 'UserID' => $user->ID]);
        $rows[] = ['department.admin@example.com', 'Department admin of Emergency (5 users)'];

        $user = $this->user('organization.admin@example.com', 'Organization', 'Admin', $deptIds['Nursing']);
        $this->membership('OrganizationAdmins', ['OrganizationID' => 1, 'UserID' => $user->ID]);
        $rows[] = ['organization.admin@example.com', 'Org admin of Org 1 (Emergency, Cardiology, Nursing)'];

        $user = $this->user('system.admin@example.com', 'System', 'Admin', $deptIds['Nursing']);
        $this->membership('SystemAdmins', ['SystemID' => $systemId, 'UserID' => $user->ID]);
        $rows[] = ['system.admin@example.com', 'System admin (Orgs 1 + 933, NOT Org 2)'];

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
        $id = DB::table('Departments')
            ->where(['Name' => $name, 'OrganizationID' => $orgId])
            ->value('ID');

        return $id ?? DB::table('Departments')->insertGetId([
            'Name' => $name,
            'OrganizationID' => $orgId,
        ]);
    }

    private function fillDepartment(int $deptId, string $deptName, int $count): void
    {
        $slug = Str::lower($deptName);
        for ($i = 1; $i <= $count; $i++) {
            $this->user("$slug.user$i@example.com", ucfirst($slug), "User$i", $deptId);
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
