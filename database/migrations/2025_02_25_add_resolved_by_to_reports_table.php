<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            $table->dropForeign(['resolved_by']);
            // Drop the existing resolved_by column if it exists as a regular column
            $table->dropColumn('resolved_by');
            
            // Add the resolved_by column as a foreign key
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['resolved_by']);
            $table->dropColumn('resolved_by');
        });
    }
};