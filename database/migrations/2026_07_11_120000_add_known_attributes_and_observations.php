<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * known_attributes: the fast-read set the routing-rule-editor dropdown
     * consumes. saml_attribute_observations: the names-only capture history
     * that backs it (NEVER stores attribute values — that's PHI). Spec:
     * docs/specs/2026-07-11-known-attributes.md
     */
    public function up(): void
    {
        if (! Schema::hasColumn('saml_clients', 'known_attributes')) {
            Schema::table('saml_clients', function (Blueprint $table) {
                $table->json('known_attributes')->nullable()->after('attribute_map');
            });
        }

        Schema::create('saml_attribute_observations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saml_client_id');
            $table->string('name');
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedInteger('observation_count')->default(0);
            $table->timestamps();
            $table->unique(['saml_client_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saml_attribute_observations');
        if (Schema::hasColumn('saml_clients', 'known_attributes')) {
            Schema::table('saml_clients', function (Blueprint $table) {
                $table->dropColumn('known_attributes');
            });
        }
    }
};
