<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('opener_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->string('status')->default('open'); // open, closed
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['opener_id', 'created_at']);
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
