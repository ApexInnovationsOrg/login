<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saml_clients', function (Blueprint $table) {
            // Marks a client that asserts Employee (admin portal) identities
            // rather than Users; see docs/specs/2026-07-09-admin-portal-sso.md
            $table->boolean('admin_portal')->default(false)->after('jit_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('saml_clients', function (Blueprint $table) {
            $table->dropColumn('admin_portal');
        });
    }
};
