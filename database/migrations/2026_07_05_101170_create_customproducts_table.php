<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This table has no legacy class definition; columns are inferred from
     * the raw SQL that uses it. Local development and testing only.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('CustomProducts', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('ProductID')->default(0);
            $table->integer('OrganizationID')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CustomProducts');
    }
}
