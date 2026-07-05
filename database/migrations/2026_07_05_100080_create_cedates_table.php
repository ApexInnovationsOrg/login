<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCEDatesTable extends Migration
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
        Schema::create('CEDates', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('ProductID')->default(0);
            $table->integer('BadgeID')->default(0);
            $table->dateTime('Original')->nullable();
            $table->dateTime('Last')->nullable();
            $table->dateTime('Expire')->nullable();
            $table->string('Label')->nullable();
            $table->integer('BadgeOrder')->default(0);
            $table->enum('PendingApproval', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CEDates');
    }
}
