<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the existing status column and create a new one with check constraint
            $table->dropColumn('status');
            $table->string('status')->default('pending')->checkIn(['pending', 'in_progress', 'resolved', 'closed']);
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Convert back to a regular string column
            $table->dropColumn('status');
            $table->string('status')->default('pending');
        });
    }
};