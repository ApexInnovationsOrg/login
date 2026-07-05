<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNoticesTable extends Migration
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
        Schema::create('Notices', function (Blueprint $table) {
            $table->increments('ID');
            $table->text('Message')->nullable(); // NOT NULL in prod (implicit '' when omitted, non-strict MySQL)
            $table->dateTime('Date')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Notices');
    }
}
