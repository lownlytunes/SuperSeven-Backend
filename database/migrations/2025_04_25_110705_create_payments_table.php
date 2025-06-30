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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_id')->index();
            $table->date('payment_date');
            $table->decimal('amount_paid', 8, 2);
            $table->smallInteger('payment_status')->default(0);
            $table->string('remarks', 100)->nullable();
            $table->timestamps();

            $table->foreign('billing_id')->references('id')->on('billings')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
