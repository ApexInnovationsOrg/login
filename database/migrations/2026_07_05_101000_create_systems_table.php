<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemsTable extends Migration
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
        Schema::create('Systems', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('Name')->default('');
            $table->dateTime('CreationDate')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->text('Comments')->nullable();
            $table->text('CurriculumNotice')->nullable();
            $table->dateTime('CurriculumDate')->nullable();
            $table->text('CommunityNotice')->nullable();
            $table->dateTime('CommunityDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Systems');
    }
}
