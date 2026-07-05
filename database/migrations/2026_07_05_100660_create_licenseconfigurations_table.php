<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLicenseConfigurationsTable extends Migration
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
        Schema::create('LicenseConfigurations', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('LicenseID')->default(0);
            $table->integer('CourseID')->default(0);
            $table->integer('Level')->default(0);
            $table->enum('AllowCourse', ['Y', 'N'])->default('Y');
            $table->enum('AllowTest', ['Y', 'N'])->default('Y');
            $table->enum('AllowCertificate', ['Y', 'N'])->default('Y');
            $table->enum('RandomizeAnswers', ['Y', 'N'])->default('Y');
            $table->enum('RandomizeQuestions', ['Y', 'N'])->default('Y');
            $table->enum('ForceTimeInCourse', ['Y', 'N'])->default('N');
            $table->integer('ForceTime')->default(0);
            $table->integer('PassingScore')->default(80);
            $table->string('XMLFile')->default('release.xml');
            $table->integer('MaxFailsPerPeriod')->default(0);
            $table->integer('MaxAttempts')->default(3);
            $table->integer('Clears')->default(2);
            $table->integer('MinutesBetweenTests')->default(0);
            $table->string('IPRestrictions')->default('');
            $table->enum('EnforceIP', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LicenseConfigurations');
    }
}
