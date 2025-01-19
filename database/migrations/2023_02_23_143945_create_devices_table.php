<?php
// 2023_02_23_143945_create_devices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'office_id')) {
                $table->foreignId('office_id')->constrained()->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'office_id')) {
                $table->dropForeign('devices_office_id_foreign');
                $table->dropColumn('office_id');
            }
        });
    }
}