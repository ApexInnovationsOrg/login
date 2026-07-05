<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCPELogsTable extends Migration
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
        Schema::create('CPELogs', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UserID')->default(0);
            $table->integer('TestResultID')->nullable();
            $table->enum('NIHSS', ['Y', 'N'])->default('N');
            $table->string('Response')->default('');
            $table->dateTime('DateSent')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->enum('Success', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CPELogs');
    }
}
