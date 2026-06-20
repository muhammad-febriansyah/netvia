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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // super_admin gets all access via Gate::before bypass (no explicit assignment needed).
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'paket.view', 'paket.create', 'paket.update', 'paket.delete',
            'pelanggan.view', 'pelanggan.create', 'pelanggan.update', 'pelanggan.delete',
            'tagihan.view', 'tagihan.create', 'tagihan.update', 'tagihan.void', 'tagihan.generate',
            'pembayaran.view', 'pembayaran.konfirmasi', 'pembayaran.create_qris',
            'notifikasi.view', 'notifikasi.kirim', 'notifikasi.template',
            'laporan.view', 'laporan.export',
        ]);

        $finance = Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web']);
        $finance->syncPermissions([
            'paket.view',
            'pelanggan.view',
            'tagihan.view', 'tagihan.void',
            'pembayaran.view', 'pembayaran.konfirmasi', 'pembayaran.create_qris',
            'notifikasi.view',
            'laporan.view', 'laporan.export',
        ]);
    }
}
