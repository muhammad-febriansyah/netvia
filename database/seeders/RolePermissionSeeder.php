<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'paket.view', 'paket.create', 'paket.update', 'paket.delete',
            'pelanggan.view', 'pelanggan.create', 'pelanggan.update', 'pelanggan.delete',
            'tagihan.view', 'tagihan.create', 'tagihan.update', 'tagihan.void', 'tagihan.generate',
            'pembayaran.view', 'pembayaran.konfirmasi', 'pembayaran.create_qris',
            'notifikasi.view', 'notifikasi.kirim', 'notifikasi.template',
            'laporan.view', 'laporan.export',
            'pengaturan.view', 'pengaturan.update',
            'user.view', 'user.create', 'user.update', 'user.delete',
            'pemutusan.kelola',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Staff. Admin has full access via Gate::before bypass (no explicit assignment needed).
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Customer (pelanggan self-service portal). Access is gated by role, not permissions.
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}
