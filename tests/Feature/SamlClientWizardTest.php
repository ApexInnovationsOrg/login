<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\SamlClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SamlClientWizardTest extends TestCase
{
    use RefreshDatabase;

    private function seedOrgAndDept(): void
    {
        Organization::factory()->create(['ID' => 4242, 'Name' => 'Wizard Acme Health']);
        Department::factory()->create([
            'ID' => 5000, 'OrganizationID' => 4242, 'Name' => 'Wizard Cardiology', 'Active' => 'Y',
        ]);
    }

    public function test_wizard_creates_client_with_department_and_jit(): void
    {
        $this->seedOrgAndDept();

        $this->artisan('saml:client', ['action' => 'create', '--wizard' => true])
            ->expectsQuestion('Client display name', 'Wizard Acme Health')
            ->expectsQuestion('URL slug', 'wizard-acme-health')
            ->expectsSearch('Organization', '4242', 'Wizard Acme', ['4242' => 'Wizard Acme Health'])
            ->expectsSearch('Default department', '5000', '', [
                '' => 'None — users choose at finish-account',
                '5000' => 'Wizard Cardiology',
            ])
            ->expectsConfirmation('Auto-create unknown users on first login?', 'yes')
            ->expectsConfirmation('Customize attribute names? (needed for Entra/Azure)', 'no')
            ->expectsOutputToContain('/saml/wizard-acme-health/acs')
            ->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', [
            'slug' => 'wizard-acme-health',
            'organization_id' => 4242,
            'department_id' => 5000,
            'jit_enabled' => true,
            'enabled' => false,
        ]);
    }

    public function test_wizard_none_department_stores_null(): void
    {
        $this->seedOrgAndDept();

        $this->artisan('saml:client', ['action' => 'create', '--wizard' => true])
            ->expectsQuestion('Client display name', 'Wizard Acme Health')
            ->expectsQuestion('URL slug', 'wizard-acme-health')
            ->expectsSearch('Organization', '4242', 'Wizard Acme', ['4242' => 'Wizard Acme Health'])
            ->expectsSearch('Default department', '', '', [
                '' => 'None — users choose at finish-account',
                '5000' => 'Wizard Cardiology',
            ])
            ->expectsConfirmation('Auto-create unknown users on first login?', 'yes')
            ->expectsConfirmation('Customize attribute names? (needed for Entra/Azure)', 'no')
            ->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'wizard-acme-health', 'department_id' => null]);
    }

    public function test_wizard_custom_attribute_map(): void
    {
        $this->seedOrgAndDept();

        $this->artisan('saml:client', ['action' => 'create', '--wizard' => true])
            ->expectsQuestion('Client display name', 'Entra Corp')
            ->expectsQuestion('URL slug', 'entra-corp')
            ->expectsSearch('Organization', '4242', 'Wizard Acme', ['4242' => 'Wizard Acme Health'])
            ->expectsSearch('Default department', '', '', [
                '' => 'None — users choose at finish-account',
                '5000' => 'Wizard Cardiology',
            ])
            ->expectsConfirmation('Auto-create unknown users on first login?', 'yes')
            ->expectsConfirmation('Customize attribute names? (needed for Entra/Azure)', 'yes')
            ->expectsQuestion('Email attribute name', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress')
            ->expectsQuestion('First name attribute name', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname')
            ->expectsQuestion('Last name attribute name', 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname')
            ->assertSuccessful();

        $client = SamlClient::where('slug', 'entra-corp')->first();
        // Key order is irrelevant for an attribute map; compare by key/value.
        $this->assertEquals([
            'email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
            'first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
            'last_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
        ], $client->attribute_map);
    }

    public function test_flag_based_create_still_works(): void
    {
        $this->artisan('saml:client', ['action' => 'create', '--name' => 'Plain Co', '--org' => 7])
            ->expectsOutputToContain('/saml/plain-co/acs')
            ->assertSuccessful();

        $this->assertDatabaseHas('saml_clients', ['slug' => 'plain-co', 'organization_id' => 7]);
    }
}
