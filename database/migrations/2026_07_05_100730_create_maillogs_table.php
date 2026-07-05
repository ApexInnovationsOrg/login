<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailLogsTable extends Migration
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
        Schema::create('MailLogs', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('To')->nullable();
            $table->string('Subject')->nullable();
            $table->dateTime('Date')->nullable();
            $table->enum('Success', ['Y', 'N'])->default('N');
            $table->text('Message')->nullable();
            $table->string('MessageID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('MailLogs');
    }
}
