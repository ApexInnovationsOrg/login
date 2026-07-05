<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
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
        Schema::create('Departments', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('OrganizationID')->default(0);
            $table->string('Name')->default('');
            $table->integer('LMSRestrictionID')->nullable();
            $table->text('CurriculumNotice')->nullable();
            $table->dateTime('CurriculumDate')->nullable();
            $table->text('CommunityNotice')->nullable();
            $table->dateTime('CommunityDate')->nullable();
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
        Schema::dropIfExists('Departments');
    }
}
