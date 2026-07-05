<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminEventsTable extends Migration
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
        Schema::create('AdminEvents', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('EmployeeID')->default(0);
            $table->integer('EventTypeID')->default(0);
            $table->string('IPAddress')->default('');
            $table->text('Notes')->nullable();
            $table->dateTime('EventDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->string('EventInfo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('AdminEvents');
    }
}
