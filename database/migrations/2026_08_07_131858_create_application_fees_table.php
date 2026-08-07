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
        Schema::create('application_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_fee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('fee_type', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount');
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['application_id', 'sort_order']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_fees');
    }
};
