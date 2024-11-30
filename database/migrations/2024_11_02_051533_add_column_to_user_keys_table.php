<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_keys');

        Schema::create('user_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('public_key');
            $table->text('private_key');
            $table->timestamps();
            $table->softDeletes(); // Untuk fitur soft delete

            // Tambahkan index untuk optimasi query
            $table->index(['user_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_keys');
    }
};
