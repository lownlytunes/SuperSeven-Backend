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
        Schema::dropIfExists('sessions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->text('payload');
            $table->integer('last_activity');
            $table->primary('id');
        });
        // Note: The session table structure may vary based on your application's requirements.
        // Adjust the columns as necessary to match your session storage needs.
        // This is a basic example and may not include all necessary fields for your application.
        // Ensure to add any additional fields that your application requires for session management.
        // For example, you might want to include user ID, IP address, or other relevant data.
        // Always test the migration to ensure it meets your application's needs.
    }
};
