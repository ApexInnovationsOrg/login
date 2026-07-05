<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseKeysTable extends Migration
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
        Schema::create('LicenseKeys', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('LicensePeriodID')->default(0);
            $table->string('Key')->default('');
            $table->integer('DepartmentID')->nullable();
            $table->integer('Iteration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LicenseKeys');
    }
}
