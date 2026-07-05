<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccreditationsTable extends Migration
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
        Schema::create('Accreditations', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('OrganizationID')->nullable();
            $table->integer('DepartmentID')->nullable();
            $table->enum('ACCChestPain', ['Y', 'N'])->default('N');
            $table->enum('ACCHeartFailure', ['Y', 'N'])->default('N');
            $table->enum('ACCAtrialFib', ['Y', 'N'])->default('N');
            $table->enum('ACCCardiacCathLab', ['Y', 'N'])->default('N');
            $table->enum('ACCHeartCARE', ['Y', 'N'])->default('N');
            $table->enum('ACCChestPainCPCenter', ['Y', 'N'])->default('N');
            $table->enum('ACCChestPainCPwPCI', ['Y', 'N'])->default('N');
            $table->enum('ACCChestPainCPwResus', ['Y', 'N'])->default('N');
            $table->enum('ACCChestPainCriticalAccess', ['Y', 'N'])->default('N');
            $table->enum('ACCChestPainFreeStandED', ['Y', 'N'])->default('N');
            $table->enum('ACCHeartFailureHFwOutput', ['Y', 'N'])->default('N');
            $table->enum('AHAMissionLifeline', ['Y', 'N'])->default('N');
            $table->enum('DNVStrokeReady', ['Y', 'N'])->default('N');
            $table->enum('DNVPrimarySC', ['Y', 'N'])->default('N');
            $table->enum('DNVCompSC', ['Y', 'N'])->default('N');
            $table->enum('DNVStrokePrimaryPlus', ['Y', 'N'])->default('N');
            $table->enum('DNVHeartFailure', ['Y', 'N'])->default('N');
            $table->enum('HFAPStrokeReady', ['Y', 'N'])->default('N');
            $table->enum('HFAPPrimaryStroke', ['Y', 'N'])->default('N');
            $table->enum('HFAPCompStroke', ['Y', 'N'])->default('N');
            $table->enum('HFAPThrombSC', ['Y', 'N'])->default('N');
            $table->enum('Magnet', ['Y', 'N'])->default('N');
            $table->enum('StateCertifiedSC', ['Y', 'N'])->default('N');
            $table->enum('TJCPrimarySC', ['Y', 'N'])->default('N');
            $table->enum('TJCCompSC', ['Y', 'N'])->default('N');
            $table->enum('TJCHeartFailure', ['Y', 'N'])->default('N');
            $table->enum('TJCStrokeRdy', ['Y', 'N'])->default('N');
            $table->enum('TJCSepsis', ['Y', 'N'])->default('N');
            $table->enum('TJCChestPain', ['Y', 'N'])->default('N');
            $table->enum('TJCThrombectomyCapable', ['Y', 'N'])->default('N');
            $table->enum('TJCChestPainHAReady', ['Y', 'N'])->default('N');
            $table->enum('TJCChestPainPrimaryC', ['Y', 'N'])->default('N');
            $table->enum('TJCChestPainCompCenter', ['Y', 'N'])->default('N');
            $table->enum('StateCertifiedCP', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Accreditations');
    }
}
