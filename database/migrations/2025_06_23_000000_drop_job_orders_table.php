<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('job_orders');
    }

    public function down(): void
    {
        // Optionally, you can recreate the table here if needed
    }
};
