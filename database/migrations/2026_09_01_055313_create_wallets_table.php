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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();

            // financials
            $table->decimal('balance',15,2)->default(0.00);

            // security
            $table->enum('status', ['active', 'frozen', 'suspended'])->default('active'); // active, frozen, suspended
            $table->string('nonce',64)->nullable();

            // timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
