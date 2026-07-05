<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCPECredentialsTable extends Migration
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
        Schema::create('CPECredentials', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UserID')->default(0);
            $table->integer('NABPePID')->default(0);
            $table->dateTime('DOB')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->dateTime('DateCheck')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->enum('Valid', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CPECredentials');
    }
}
