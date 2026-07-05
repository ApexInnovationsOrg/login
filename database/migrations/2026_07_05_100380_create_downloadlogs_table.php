<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadLogsTable extends Migration
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
        Schema::create('DownloadLogs', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UploadID')->default(0);
            $table->string('DownloadDate')->nullable();
            $table->integer('IPAddress')->nullable();
            $table->string('Referrer')->nullable();
            $table->integer('UserID')->nullable();
            $table->string('UserAgent')->nullable();
            $table->string('FileName')->nullable();
            $table->integer('UploadTypeID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('DownloadLogs');
    }
}
