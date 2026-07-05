<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Schema mirrors the shared production database (maintained by hand);
     * these migrations exist for local development and testing only and
     * must never be run against the shared production database.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('OrganizationAdmins', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('OrganizationID')->default(0);
            $table->integer('UserID')->default(0);
            $table->integer('SeeEvaluations')->default(0);
            $table->enum('SeeNewUsers', ['Y', 'N'])->default('Y');
            $table->enum('CanMoveSeats', ['Y', 'N'])->default('N');
            $table->string('AllowedProductIDs')->nullable();
            $table->enum('examFailEmail', ['Y', 'N'])->default('N');
            $table->enum('MaxAttempts', ['Y', 'N'])->default('Y');
            $table->enum('LicenseInfo', ['Y', 'N'])->default('Y');
            $table->enum('EssayEmail', ['Y', 'N'])->default('N');
            $table->enum('TestCompleted', ['Y', 'N'])->default('N');
            $table->enum('MainContact', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('OrganizationAdmins');
    }
}
