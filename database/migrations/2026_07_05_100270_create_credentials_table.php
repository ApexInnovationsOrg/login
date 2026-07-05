<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialsTable extends Migration
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
        Schema::create('Credentials', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Name')->default('');
            $table->enum('CEH', ['Y', 'N'])->default('N');
            $table->enum('CNE', ['Y', 'N'])->default('N');
            $table->enum('CME', ['Y', 'N'])->default('N');
            $table->enum('CAPT', ['Y', 'N'])->default('N');
            $table->enum('OHPT', ['Y', 'N'])->default('N');
            $table->enum('PNPT', ['Y', 'N'])->default('N');
            $table->enum('CPE', ['Y', 'N'])->default('N');
            $table->integer('AccreditorID')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Credentials');
    }
}
