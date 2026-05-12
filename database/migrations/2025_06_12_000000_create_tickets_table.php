<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->index();
            $table->text('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Reporter (who created the ticket)
            $table->foreignId('device_id')->constrained()->onDelete('cascade'); // Associated device
            $table->foreignId('office_id')->constrained()->onDelete('cascade'); // Originating office
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null'); // Assigned technician
            
            // Ticket lifecycle
            $table->enum('status', ['open', 'in-progress', 'resolved', 'closed'])->default('open')->index();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->index();
            
            // Resolution tracking
            $table->text('resolution_notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            // Image support
            $table->string('image')->nullable();
            
            // Timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
