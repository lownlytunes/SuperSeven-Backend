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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('package_id')->index();
            $table->date('booking_date');
            $table->string('event_name', 100)->nullable();
            $table->string('booking_address', 100);
            $table->smallInteger('booking_status')->default(0);
            $table->smallInteger('deliverable_status')->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->date('completion_date')->nullable();

            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
