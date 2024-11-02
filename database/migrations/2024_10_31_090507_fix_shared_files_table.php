<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('shared_files');

        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('data_access_requests')->onDelete('cascade');
            $table->foreignId('encrypted_file_id')->constrained('encrypted_files')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_files');
    }
};
