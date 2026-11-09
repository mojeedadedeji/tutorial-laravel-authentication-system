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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('price'); // in Naira (kobo conversion deferred)
            $table->string('category');
            $table->string('condition'); // New | Like New | Good | Fair
            $table->string('brand')->nullable();
            $table->string('size')->nullable();
            $table->string('location');
            $table->string('delivery_mode')->default('pickup'); // pickup | delivery | both
            $table->string('delivery_note')->nullable();
            $table->json('photos')->nullable(); // array of image URLs/paths
            $table->string('status')->default('active')->index(); // active | sold | draft
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
