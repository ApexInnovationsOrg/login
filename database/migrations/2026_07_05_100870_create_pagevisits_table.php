<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageVisitsTable extends Migration
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
        Schema::create('PageVisits', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->default(0);
            $table->integer('UserID')->default(0);
            $table->integer('PageID')->default(0);
            $table->dateTime('TimeIn')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->integer('SecondsIn')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('PageVisits');
    }
}
