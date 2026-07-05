<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
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
        Schema::create('Uploads', function (Blueprint $table) {
            $table->increments('ID');
            $table->dateTime('Uploaded')->nullable(); // NOT NULL in prod (zero-date when omitted, non-strict MySQL)
            $table->integer('UserID')->nullable();
            $table->integer('OrganizationID')->nullable();
            $table->integer('UploadTypeID')->nullable();
            $table->integer('ProductID')->nullable();
            $table->string('OriginalName')->default('');
            $table->string('Extension')->default('');
            $table->enum('System', ['Y', 'N'])->default('N');
            $table->enum('Public', ['Y', 'N'])->default('N');
            $table->integer('Size')->default(0);
            $table->integer('Height')->default(0);
            $table->integer('Width')->default(0);
            $table->string('ContentType')->default('');
            $table->string('Descriptor')->nullable();
            $table->text('Description')->nullable();
            $table->string('Locator')->default('');
            $table->enum('AnyoneCanDownload', ['Y', 'N'])->default('N');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Uploads');
    }
}
