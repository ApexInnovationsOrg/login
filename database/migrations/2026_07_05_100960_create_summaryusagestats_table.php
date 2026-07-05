<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSummaryUsageStatsTable extends Migration
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
        Schema::create('SummaryUsageStats', function (Blueprint $table) {
            $table->increments('ID');
            $table->dateTime('Date')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->integer('NumUsers')->default(0);
            $table->integer('NumLMSUsers')->nullable();
            $table->integer('NumTests')->default(0);
            $table->integer('NumNIHTests')->default(0);
            $table->integer('HoursIn')->default(0);
            $table->integer('MonthlyUsers')->nullable();
            $table->integer('MonthlyLMSUsers')->nullable();
            $table->integer('YearlyUsers')->nullable();
            $table->integer('YearlyLMSUsers')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SummaryUsageStats');
    }
}
