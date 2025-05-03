<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDevicesTable extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->foreignId('device_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_subcategory_id')->constrained()->onDelete('cascade');
            $table->foreignId('office_id')->constrained()->onDelete('cascade');
            $table->string('serial_number');
            $table->string('model_number');
            $table->string('manufacturer');
            $table->string('status');
            $table->string('image')->nullable();
            $table->index('device_category_id');
            $table->index('device_type_id');
            $table->index('device_subcategory_id');
            $table->index('office_id');
            $table->index('status');
            $table->timestamps();
        });


    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
