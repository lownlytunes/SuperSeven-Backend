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
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The down method is intentionally left empty as the cache table is no longer needed.
        // If you need to recreate the cache table, you can do so by creating a new
        // migration with the appropriate structure.
        // This migration is meant to remove the cache functionality from the application.
        // Ensure that your application does not rely on the cache table anymore.
        // If you need to restore caching, consider creating a new migration to set up the cache
        // table with the necessary fields and structure.
        // This migration is a one-way operation to remove the cache functionality.
        // Always ensure that your application does not depend on the cache table before running this migration.
    }
};
