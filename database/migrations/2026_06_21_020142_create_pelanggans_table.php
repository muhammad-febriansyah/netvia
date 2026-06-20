<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan')->unique();
            $table->string('nama', 100);
            $table->string('no_wa');
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->foreignId('paket_id')->constrained('pakets');
            $table->date('tanggal_aktivasi');
            $table->unsignedTinyInteger('tgl_jatuh_tempo');
            $table->enum('status', ['aktif', 'nonaktif', 'isolir'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('tgl_jatuh_tempo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggans');
    }
};
