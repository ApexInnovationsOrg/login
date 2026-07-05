<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationsTable extends Migration
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
        Schema::create('Organizations', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Name')->default('');
            $table->string('Address')->default('');
            $table->string('Address2')->nullable();
            $table->string('City')->default('');
            $table->integer('StateID')->nullable();
            $table->string('PostalCode')->default('');
            $table->integer('CountryID')->default(0);
            $table->string('Phone')->default('');
            $table->dateTime('CreationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->text('Comments')->nullable();
            $table->text('CurriculumNotice')->nullable();
            $table->dateTime('CurriculumDate')->nullable();
            $table->text('CommunityNotice')->nullable();
            $table->dateTime('CommunityDate')->nullable();
            $table->enum('Demo', ['Y', 'N'])->default('N');
            $table->enum('RequireEmployeeNum', ['Y', 'N'])->default('N');
            $table->integer('CoursewareTimeout')->default(15);
            $table->integer('PasswordExpirationDays')->default(0);
            $table->integer('PasswordMinLength')->default(6);
            $table->integer('PasswordHistoryLength')->default(0);
            $table->integer('PasswordLockoutAttempts')->default(0);
            $table->integer('PasswordLockoutDuration')->default(0);
            $table->enum('PasswordComplexityNumeric', ['Y', 'N'])->default('N');
            $table->enum('PasswordComplexitySpecial', ['Y', 'N'])->default('N');
            $table->enum('PasswordComplexityUppercase', ['Y', 'N'])->default('N');
            $table->enum('PasswordComplexityLowercase', ['Y', 'N'])->default('N');
            $table->enum('PasswordComplexityNoUserInfo', ['Y', 'N'])->default('N');
            $table->string('IPRange')->nullable();
            $table->enum('ForceIPRangeLogin', ['Y', 'N'])->default('N');
            $table->enum('Active', ['Y', 'N'])->default('Y');
            $table->enum('TestQuestionExclusion', ['Y', 'N'])->default('N');
            $table->enum('AllowFullDepartmentSeats', ['Y', 'N'])->default('Y');
            $table->enum('AllowEHAC', ['Y', 'N'])->default('Y');
            $table->enum('AllowNIHSS', ['Y', 'N'])->default('Y');
            $table->double('Latitude')->nullable();
            $table->double('Longitude')->nullable();
            $table->enum('ApexCommunity', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Organizations');
    }
}
