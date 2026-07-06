<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saml_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('enabled')->default(true);
            $table->string('idp_entity_id');
            $table->string('idp_sso_url');
            $table->text('idp_certificate');
            $table->integer('organization_id');
            $table->integer('department_id')->nullable();
            $table->boolean('jit_enabled')->default(false);
            $table->json('attribute_map');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saml_clients');
    }
};
