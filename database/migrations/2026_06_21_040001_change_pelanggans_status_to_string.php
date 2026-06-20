<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen status from enum to string so the new `pending` state (customer
     * self-registration awaiting admin activation) is allowed.
     */
    public function up(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->string('status', 20)->default('aktif')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggans', function (Blueprint $table) {
            $table->enum('status', ['aktif', 'nonaktif', 'isolir'])->default('aktif')->change();
        });
    }
};
