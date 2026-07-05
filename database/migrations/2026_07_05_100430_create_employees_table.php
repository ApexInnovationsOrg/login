<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
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
        Schema::create('Employees', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('FirstName')->default('');
            $table->string('LastName')->default('');
            $table->string('Email')->default('');
            $table->string('Password')->default('');
            $table->dateTime('PasswordLastChanged')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->enum('SalesRep', ['Y', 'N'])->default('N');
            $table->dateTime('Snooze')->nullable();
            $table->integer('EmployeeDepartmentID')->default(0);
            $table->string('Biography')->default('');
            $table->string('PictureLocation')->default('');
            $table->string('Title')->default('');
            $table->string('Credentials')->default('');
            $table->integer('Level')->default(0);
            $table->integer('Order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Employees');
    }
}
