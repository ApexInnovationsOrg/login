<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLMSRestrictionsTable extends Migration
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
        Schema::create('LMSRestrictions', function (Blueprint $table) {
            $table->increments('ID');
            $table->dateTime('StartDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->integer('MaxUsers')->default(0);
            $table->string('UserList')->nullable();
            $table->string('FirstLoginIP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('LMSRestrictions');
    }
}
