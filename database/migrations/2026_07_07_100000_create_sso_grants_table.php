<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_grants', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');           // Users.ID (legacy schema, no FK constraint)
            $table->integer('organization_id');
            $table->string('granted_by');         // acting admin identity (Employees world, no FK)
            $table->timestamps();
            $table->unique(['user_id', 'organization_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_grants');
    }
};
