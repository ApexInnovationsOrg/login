<?php

namespace Tests\Feature;

use App\Models\Credential;
use App\Models\CredentialLicenseTypes;
use App\Models\Department;
use App\Models\Organization;
use App\Models\ProfessionalCredentialFilters;
use App\Models\ProfessionalRole;
use App\Models\SamlClient;
use App\Models\States;
use App\Models\System;
use App\Models\User;
use Tests\TestCase;

class LegacyModelConventionTest extends TestCase
{
    public static function legacyModels(): array
    {
        return [
            [Organization::class], [Department::class], [User::class], [System::class],
            [Credential::class], [ProfessionalRole::class], [States::class],
            [CredentialLicenseTypes::class], [ProfessionalCredentialFilters::class],
        ];
    }

    /** @dataProvider legacyModels */
    public function test_legacy_models_use_i_d_primary_key(string $class): void
    {
        $this->assertSame('ID', (new $class)->getKeyName());
    }

    public function test_non_timestamped_legacy_models_disable_timestamps(): void
    {
        $this->assertFalse((new Organization)->usesTimestamps());
        $this->assertFalse((new Department)->usesTimestamps());
        $this->assertFalse((new Credential)->usesTimestamps());
        $this->assertFalse((new States)->usesTimestamps());
        $this->assertFalse((new CredentialLicenseTypes)->usesTimestamps());
        $this->assertFalse((new ProfessionalRole)->usesTimestamps());
        $this->assertFalse((new ProfessionalCredentialFilters)->usesTimestamps());
    }

    public function test_app_owned_saml_client_keeps_stock_conventions(): void
    {
        $this->assertSame('id', (new SamlClient)->getKeyName());
        $this->assertTrue((new SamlClient)->usesTimestamps());
    }
}
