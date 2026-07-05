<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
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
        Schema::create('Products', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Name')->default('');
            $table->enum('Registered', ['Y', 'N'])->default('N');
            $table->string('TagLine')->nullable();
            $table->text('Description')->nullable();
            $table->integer('MaxCourses')->default(8);
            $table->string('PathToUserGuide')->nullable();
            $table->string('DemoKey')->nullable();
            $table->enum('CME', ['Y', 'N'])->default('N');
            $table->enum('CNE', ['Y', 'N'])->default('N');
            $table->enum('CEH', ['Y', 'N'])->default('N');
            $table->enum('CAPT', ['Y', 'N'])->default('N');
            $table->enum('CPE', ['Y', 'N'])->default('N');
            $table->enum('Active', ['Y', 'N'])->default('Y');
            $table->enum('Beta', ['Y', 'N'])->default('N');
            $table->double('Version')->default(3);
            $table->enum('DefaultButtons', ['Y', 'N'])->default('Y');
            $table->string('PathToLogo')->nullable();
            $table->string('PathToLogoLarge')->nullable();
            $table->double('UnitPrice')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Products');
    }
}
