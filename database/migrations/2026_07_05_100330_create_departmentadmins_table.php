<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentAdminsTable extends Migration
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
        Schema::create('DepartmentAdmins', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('DepartmentID')->default(0);
            $table->integer('UserID')->default(0);
            $table->integer('SeeEvaluations')->default(0);
            $table->enum('SeeNewUsers', ['Y', 'N'])->default('Y');
            $table->enum('examFailEmail', ['Y', 'N'])->default('N');
            $table->enum('MaxAttempts', ['Y', 'N'])->default('Y');
            $table->enum('EssayEmail', ['Y', 'N'])->default('N');
            $table->enum('TestCompleted', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DepartmentAdmins');
    }
}
