<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
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
        Schema::create('Courses', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('ProductID')->default(1);
            $table->string('Name')->default('');
            $table->string('LongName')->nullable();
            $table->integer('Level')->default(0);
            $table->enum('IPCEEvaluation', ['Y', 'N'])->default('N');
            $table->enum('IPCECertificate', ['Y', 'N'])->default('N');
            $table->enum('NIH', ['Y', 'N'])->default('N');
            $table->integer('QuestionsOnTest')->default(0);
            $table->integer('DefaultPassingScore')->default(0);
            $table->integer('TestMinutesAllowed')->default(0);
            $table->double('ContentHours')->nullable();
            $table->double('MaxCMEHours')->nullable();
            $table->double('MaxCNEHours')->nullable();
            $table->double('MaxCEHHours')->nullable();
            $table->double('MaxCAPTHours')->nullable();
            $table->double('MaxOHPTHours')->nullable();
            $table->double('MaxFLCEHHours')->nullable();
            $table->double('MaxCPEHours')->nullable();
            $table->string('CEHNumber')->nullable();
            $table->string('CEHType')->nullable();
            $table->string('CETrackingNumber')->nullable();
            $table->enum('Enabled', ['Y', 'N'])->default('N');
            $table->integer('MinTimeInCourse')->default(30);
            $table->integer('MaxAttempts')->default(3);
            $table->integer('Clears')->default(2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Courses');
    }
}
