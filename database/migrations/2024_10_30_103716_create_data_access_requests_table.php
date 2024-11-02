<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabel untuk menyimpan public key pengguna
        Schema::create('user_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('public_key');
            $table->text('private_key');
            $table->timestamps();
        });

        // Tabel untuk menyimpan request akses data
        Schema::create('data_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('encrypted_key')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Tabel untuk menyimpan file yang dibagikan
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
        Schema::dropIfExists('data_access_requests');
        Schema::dropIfExists('user_keys');
    }
};
