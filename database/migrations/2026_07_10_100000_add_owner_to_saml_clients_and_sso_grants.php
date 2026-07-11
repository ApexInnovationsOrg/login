<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A client (and its SSO-manager grant list) is owned by an organization
     * or a system (docs/specs/2026-07-10-client-ownership.md). Nothing using
     * organization_id has deployed, so this renames rather than shims.
     */
    public function up(): void
    {
        Schema::table('saml_clients', function (Blueprint $table) {
            $table->string('owner_type', 20)->nullable()->after('enabled');
            $table->unsignedInteger('owner_id')->nullable()->after('owner_type');
        });

        DB::table('saml_clients')->update([
            'owner_type' => 'organization',
            'owner_id' => DB::raw('organization_id'),
        ]);

        Schema::table('saml_clients', function (Blueprint $table) {
            $table->string('owner_type', 20)->nullable(false)->change();
            $table->unsignedInteger('owner_id')->nullable(false)->change();
            $table->dropColumn('organization_id');
        });

        Schema::table('sso_grants', function (Blueprint $table) {
            $table->string('owner_type', 20)->nullable()->after('user_id');
            $table->unsignedInteger('owner_id')->nullable()->after('owner_type');
        });

        DB::table('sso_grants')->update([
            'owner_type' => 'organization',
            'owner_id' => DB::raw('organization_id'),
        ]);

        Schema::table('sso_grants', function (Blueprint $table) {
            $table->string('owner_type', 20)->nullable(false)->change();
            $table->unsignedInteger('owner_id')->nullable(false)->change();
            $table->dropUnique(['user_id', 'organization_id']);
            $table->unique(['user_id', 'owner_type', 'owner_id']);
            $table->dropColumn('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('sso_grants', function (Blueprint $table) {
            $table->integer('organization_id')->nullable()->after('user_id');
        });
        DB::table('sso_grants')->where('owner_type', 'organization')->update(['organization_id' => DB::raw('owner_id')]);
        DB::table('sso_grants')->where('owner_type', '!=', 'organization')->delete();
        Schema::table('sso_grants', function (Blueprint $table) {
            $table->integer('organization_id')->nullable(false)->change();
            $table->dropUnique(['user_id', 'owner_type', 'owner_id']);
            $table->unique(['user_id', 'organization_id']);
            $table->dropColumn(['owner_type', 'owner_id']);
        });

        Schema::table('saml_clients', function (Blueprint $table) {
            $table->integer('organization_id')->nullable()->after('enabled');
        });
        DB::table('saml_clients')->where('owner_type', 'organization')->update(['organization_id' => DB::raw('owner_id')]);
        DB::table('saml_clients')->where('owner_type', '!=', 'organization')->delete();
        Schema::table('saml_clients', function (Blueprint $table) {
            $table->integer('organization_id')->nullable(false)->change();
            $table->dropColumn(['owner_type', 'owner_id']);
        });
    }
};
