<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->enum('channel', ['whatsapp', 'email']);
            $table->enum('jenis', ['invoice_baru', 'reminder_h3', 'reminder_due', 'reminder_overdue', 'struk_lunas']);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->string('recipient');
            $table->text('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['tagihan_id', 'channel', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_logs');
    }
};
