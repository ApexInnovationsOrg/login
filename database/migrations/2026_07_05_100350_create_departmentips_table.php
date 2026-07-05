<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentIPsTable extends Migration
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
        Schema::create('DepartmentIPs', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('DepartmentID')->default(0);
            $table->integer('OrganizationID')->default(0);
            $table->integer('IP')->default(0);
            $table->string('Hostname')->nullable();
            $table->dateTime('Timestamp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DepartmentIPs');
    }
}
