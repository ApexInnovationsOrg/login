<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModalsTable extends Migration
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
        Schema::create('Modals', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('PageID')->default(0);
            $table->string('Element')->default('');
            $table->text('Description')->nullable();
            $table->string('Position')->nullable();
            $table->dateTime('Date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Modals');
    }
}
