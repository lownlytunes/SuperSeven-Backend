<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('unavailable_dates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('unavailable_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('reason')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }
};
