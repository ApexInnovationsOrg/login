<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saml_clients', function (Blueprint $table) {
            // Nullable: MySQL json columns cannot take a literal default.
            // The model cast + manager normalization treat null as [].
            $table->json('email_domains')->nullable()->after('attribute_map');
        });
    }

    public function down(): void
    {
        Schema::table('saml_clients', function (Blueprint $table) {
            $table->dropColumn('email_domains');
        });
    }
};
