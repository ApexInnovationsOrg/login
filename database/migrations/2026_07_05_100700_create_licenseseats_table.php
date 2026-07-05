<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseSeatsTable extends Migration
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
        Schema::create('LicenseSeats', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('LicensePeriodID')->default(0);
            $table->integer('UserID')->default(0);
            $table->dateTime('CreationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->dateTime('ExpirationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->string('DueDates')->nullable();
            $table->enum('Active', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LicenseSeats');
    }
}
