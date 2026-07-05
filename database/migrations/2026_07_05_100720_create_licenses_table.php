<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicensesTable extends Migration
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
        Schema::create('Licenses', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('ProductID')->default(0);
            $table->integer('OrganizationID')->default(0);
            $table->integer('SalesRepID')->nullable();
            $table->integer('NumberSeats')->default(0);
            $table->integer('NumSeats')->default(0);
            $table->integer('NumAdminSeats')->default(0);
            $table->dateTime('CreationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->dateTime('ExpirationDate')->nullable();
            $table->enum('Enterprise', ['Y', 'N'])->default('N');
            $table->enum('ForceIPRangeAccess', ['Y', 'N'])->default('N');
            $table->text('Notes')->nullable();
            $table->enum('Beta', ['Y', 'N'])->default('N');
            $table->enum('Active', ['Y', 'N'])->default('Y');
            $table->enum('Pretest', ['Y', 'N'])->default('N');
            $table->enum('PatientChoice', ['Y', 'N'])->default('N');
            $table->integer('TermInMonths')->default(12);
            $table->integer('NumTerms')->default(1);
            $table->double('Price')->default(0);
            $table->enum('Shareable', ['Y', 'N'])->default('N');
            $table->enum('LMSVerificationRequired', ['Y', 'N'])->default('N');
            $table->enum('InactiveByPass', ['Y', 'N'])->default('Y');
            $table->enum('RequirementsByPass', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Licenses');
    }
}
