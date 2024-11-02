<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('data_access_requests', function (Blueprint $table) {
            $table->longText('encrypted_key')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('data_access_requests', function (Blueprint $table) {
            $table->text('encrypted_key')->nullable()->change();
        });
    }
};
