<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCEBadgesTable extends Migration
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
        Schema::create('CEBadges', function (Blueprint $table) {
            $table->increments('ID');
            $table->text('Content')->nullable();
            $table->string('Type')->nullable();
            $table->string('PathToBadge')->default('images/Badges/apex_logo.gif');
            $table->integer('BadgeWidth')->default(0);
            $table->integer('BadgeHeight')->default(0);
            $table->string('Alt')->nullable();
            $table->enum('Active', ['Y', 'N'])->default('Y');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('CEBadges');
    }
}
