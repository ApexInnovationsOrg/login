<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestQuestionsTable extends Migration
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
        Schema::create('TestQuestions', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('CourseID')->default(0);
            $table->string('Question')->default('');
            $table->string('PathToDiagram')->nullable();
            $table->integer('WidthOfDiagram')->nullable();
            $table->integer('HeightOfDiagram')->nullable();
            $table->enum('Active', ['Y', 'N'])->default('N');
            $table->string('Referral')->nullable();
            $table->integer('CourseObjectiveID')->default(0);
            $table->enum('Float', ['Y', 'N']);
            $table->enum('OutcomeMeasure', ['Y', 'N'])->default('N');
            $table->string('FlashDescriptor')->nullable();
            $table->text('Notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('TestQuestions');
    }
}
