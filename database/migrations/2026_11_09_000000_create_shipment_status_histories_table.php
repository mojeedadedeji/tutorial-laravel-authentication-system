<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['shipment_id', 'created_at']);
        });

        // Backfill existing shipments with their current status
        DB::table('shipments')->orderBy('id')->each(function ($shipment) {
            DB::table('shipment_status_histories')->insert([
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'created_at' => $shipment->created_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_status_histories');
    }
};
