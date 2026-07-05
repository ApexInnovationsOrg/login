<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
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
        Schema::create('Users', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Login')->unique();
            $table->string('Password')->default('');
            $table->text('PasswordHistory')->nullable();
            $table->dateTime('PasswordLastChanged')->nullable();
            $table->dateTime('PasswordLockoutExpires')->nullable();
            $table->string('FirstName')->default('');
            $table->string('LastName')->default('');
            $table->string('Address')->default('');
            $table->string('Address2')->nullable();
            $table->string('City')->default('');
            $table->integer('StateID')->nullable();
            $table->string('PostalCode')->default('');
            $table->integer('CountryID')->default(0);
            $table->string('Phone')->default('');
            $table->string('Title')->default('');
            $table->string('EmployeeID')->nullable();
            $table->integer('CredentialID')->nullable();
            $table->integer('StateOfLicensureID')->nullable();
            $table->string('StateLicenseNumber')->nullable();
            $table->dateTime('StateLicenseExpirationDate')->nullable();
            $table->string('NREMTCertificationNumber')->nullable();
            $table->dateTime('NREMTReregistrationDate')->nullable();
            $table->string('NEMSID')->nullable();
            $table->integer('CredentialLicenseTypeID')->nullable();
            $table->integer('DepartmentID')->nullable();
            $table->dateTime('CreationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->dateTime('LastLoginDate')->nullable();
            $table->dateTime('PreviousLastLoginDate')->nullable();
            $table->integer('SecurityQuestionID')->default(0);
            $table->string('SecurityAnswer')->default('');
            $table->enum('LMS', ['Y', 'N'])->default('N');
            $table->enum('Active', ['Y', 'N'])->default('Y');
            $table->enum('Disabled', ['Y', 'N'])->default('N');
            $table->enum('Beta', ['Y', 'N'])->default('N');
            $table->string('Locale')->default('en-us');
            $table->enum('ShowDemoReporting', ['Y', 'N'])->default('N');
            $table->enum('PasswordChangedByAdmin', ['Y', 'N'])->default('N');
            $table->enum('oldUser', ['Y', 'N'])->default('N');
            $table->string('oldPassword')->nullable();
            $table->string('oldEMail')->nullable();
            $table->string('LMSEmail')->nullable();
            $table->string('remember_token', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Users');
    }
}
