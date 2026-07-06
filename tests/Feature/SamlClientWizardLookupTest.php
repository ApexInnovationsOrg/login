<?php

namespace Tests\Feature;

use App\Console\Commands\SamlClientCommand;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlClientWizardLookupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Expose the private lookup helpers for direct testing without driving the
     * full interactive prompt sequence.
     */
    private function command(): SamlClientCommand
    {
        return new class extends SamlClientCommand
        {
            public function orgs(string $search): array
            {
                return $this->wizardOrganizationOptions($search);
            }

            public function depts(int $orgId, string $search): array
            {
                return $this->wizardDepartmentOptions($orgId, $search);
            }
        };
    }

    private function org(int $id, string $name): void
    {
        Organization::factory()->create(['ID' => $id, 'Name' => $name]);
    }

    private function dept(int $id, int $orgId, string $name, string $active): void
    {
        Department::factory()->create([
            'ID' => $id, 'OrganizationID' => $orgId, 'Name' => $name, 'Active' => $active,
        ]);
    }

    public function test_organization_options_filter_by_name(): void
    {
        $this->org(1010, 'Wizard Acme Health');
        $this->org(1020, 'Wizard Beta Clinic');

        $options = $this->command()->orgs('Wizard Acme');

        $this->assertSame(['1010' => 'Wizard Acme Health'], $this->stringKeys($options));
    }

    public function test_department_options_include_none_and_only_active_for_org(): void
    {
        $this->org(1010, 'Wizard Acme Health');
        $this->org(1020, 'Wizard Beta Clinic');
        $this->dept(1100, 1010, 'Wizard Cardiology', 'Y');
        $this->dept(1101, 1010, 'Wizard Retired Ward', 'N');
        $this->dept(1200, 1020, 'Wizard Other Org Dept', 'Y');

        $options = $this->command()->depts(1010, '');

        $this->assertSame('None — users choose at finish-account', $options['none']);
        $this->assertArrayHasKey(1100, $options);
        $this->assertArrayNotHasKey(1101, $options); // inactive excluded
        $this->assertArrayNotHasKey(1200, $options); // other org excluded
    }

    public function test_department_none_option_survives_a_search_term(): void
    {
        $this->org(1010, 'Wizard Acme Health');
        $this->dept(1100, 1010, 'Wizard Cardiology', 'Y');

        $options = $this->command()->depts(1010, 'zzz-no-match');

        $this->assertArrayHasKey('none', $options); // None always present
        $this->assertArrayNotHasKey(1100, $options);
    }

    /** Normalize integer array keys to strings for a stable assertion. */
    private function stringKeys(array $options): array
    {
        $out = [];
        foreach ($options as $k => $v) {
            $out[(string) $k] = $v;
        }

        return $out;
    }
}
