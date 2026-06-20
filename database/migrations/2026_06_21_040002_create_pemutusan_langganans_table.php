<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemutusan_langganans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggans')->cascadeOnDelete();
            $table->text('alasan');
            $table->string('foto'); // storage path of supporting photo
            $table->string('status', 20)->default('pending'); // pending | approved | rejected
            $table->text('catatan_admin')->nullable();
            $table->foreignId('diproses_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diproses_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemutusan_langganans');
    }
};
