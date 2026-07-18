<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two ordered rule lists per client (docs/specs/2026-07-10-attribute-routing.md):
     * org rules place users in an organization (system-owned clients only),
     * department rules place them in a department BY NAME within the resolved
     * org — name-targeting is what lets one rule set serve every identically
     * structured org in a hospital system.
     */
    public function up(): void
    {
        Schema::create('saml_org_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saml_client_id');
            $table->unsignedInteger('position');
            $table->string('attribute');
            $table->string('operator', 20);
            $table->string('value');
            $table->integer('organization_id');
            $table->timestamps();
            $table->unique(['saml_client_id', 'position']);
        });

        Schema::create('saml_department_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saml_client_id');
            $table->unsignedInteger('position');
            $table->string('attribute');
            $table->string('operator', 20);
            $table->string('value');
            $table->string('department_name');
            $table->timestamps();
            $table->unique(['saml_client_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saml_department_rules');
        Schema::dropIfExists('saml_org_rules');
    }
};
