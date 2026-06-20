<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tagihan')->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->nullOnDelete();
            $table->date('periode');
            $table->string('paket_nama');
            $table->bigInteger('harga');
            $table->bigInteger('jumlah');
            $table->date('tanggal_terbit');
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'void'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->string('public_token')->unique();
            $table->timestamps();

            $table->unique(['pelanggan_id', 'periode']);
            $table->index('status');
            $table->index('periode');
            $table->index('tanggal_jatuh_tempo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
