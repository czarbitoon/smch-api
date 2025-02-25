<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // First, modify the status column to be a proper ENUM type
            DB::statement("ALTER TABLE reports MODIFY status ENUM('pending', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'pending'");
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Convert back to a regular string column
            DB::statement("ALTER TABLE reports MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
        });
    }
};