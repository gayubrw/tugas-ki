<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('encrypted_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('encryption_algorithm');
            $table->text('encryption_key');
            $table->text('encryption_iv')->nullable();
            $table->double('encryption_time');
            $table->double('decryption_time')->nullable();
            $table->string('file_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('encrypted_files');
    }
};
