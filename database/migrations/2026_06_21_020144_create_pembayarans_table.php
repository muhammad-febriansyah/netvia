<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihans');
            $table->enum('metode', ['qris_pakasir', 'transfer_manual', 'cash']);
            $table->bigInteger('jumlah_bayar');
            $table->enum('status', ['pending', 'success', 'failed', 'expired'])->default('pending');
            $table->string('pakasir_order_id')->nullable()->index();
            $table->string('pakasir_reference')->nullable();
            $table->text('qr_string')->nullable();
            $table->string('payment_url')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->foreignId('dikonfirmasi_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dibayar_at')->nullable();
            $table->json('raw_callback')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
