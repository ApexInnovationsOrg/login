<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCoursewarePreferencesTable extends Migration
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
        Schema::create('UserCoursewarePreferences', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UserID')->default(0);
            $table->enum('autoCloseMenu', ['Y', 'N'])->default('Y');
            $table->integer('audioVolume')->default(0);
            $table->enum('animatePageTransitions', ['Y', 'N'])->default('Y');
            $table->enum('closedCaptioning', ['Y', 'N'])->default('N');
            $table->enum('printSlideOnly', ['Y', 'N'])->default('Y');
            $table->integer('videoSelection')->default(0);
            $table->enum('pageNumberInSearch', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('UserCoursewarePreferences');
    }
}
