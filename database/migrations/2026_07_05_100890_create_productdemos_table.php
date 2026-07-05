<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductDemosTable extends Migration
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
        Schema::create('ProductDemos', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('ProductID')->default(0);
            $table->integer('UserID')->default(0);
            $table->dateTime('DemoStartDate')->nullable();
            $table->integer('DemoHours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ProductDemos');
    }
}
