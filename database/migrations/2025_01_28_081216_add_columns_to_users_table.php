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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 30)->after('name');
            $table->string('mid_name', 30)->after('first_name')->nullable();
            $table->string('last_name', 30)->after('mid_name');
            $table->string('contact_num', 12)->after('email')->nullable();
            $table->string('address', 100)->after('contact_num')->nullable();
            $table->string('status', 10)->after('contact_num')->default('active');


            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn('first_name');
            $table->dropColumn('mid_name');
            $table->dropColumn('last_name');
            $table->dropColumn('contact_num');
            $table->dropColumn('status');
        });
    }
};
