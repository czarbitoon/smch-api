<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('device_image_url')->nullable()->after('device_id');
            $table->string('report_image')->nullable()->after('description');
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('device_image_url');
            $table->dropColumn('report_image');
        });
    }
};