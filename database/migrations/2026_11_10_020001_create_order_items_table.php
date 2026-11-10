<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained('listings')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('price');
            $table->timestamps();

            $table->index('order_id');
        });

        // listing_id becomes nullable: bundle orders reference items via order_items
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable(false)->change();
        });
    }
};
