<?php

namespace App\Actions\Auth;

use App\Actions\Pelanggan\GenerateKodePelangganAction;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegisterCustomerAction
{
    public function __construct(private GenerateKodePelangganAction $generateKode) {}

    /**
     * Register a new customer: create a pending pelanggan and a linked customer
     * user account. Admin activates the pelanggan afterwards.
     *
     * @param  array{name: string, email: string, password: string, no_wa: string, alamat: ?string, paket_id: int|string, tgl_jatuh_tempo: int}  $data
     */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $pelanggan = Pelanggan::create([
                'kode_pelanggan' => $this->generateKode->execute(),
                'nama' => $data['name'],
                'no_wa' => $data['no_wa'],
                'email' => $data['email'],
                'alamat' => $data['alamat'] ?? null,
                'paket_id' => $data['paket_id'],
                'tanggal_aktivasi' => Carbon::now('Asia/Jakarta')->toDateString(),
                'tgl_jatuh_tempo' => $data['tgl_jatuh_tempo'],
                'status' => 'pending',
            ]);

            $user = User::create([
                'pelanggan_id' => $pelanggan->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $user->assignRole('customer');

            return $user;
        });
    }
}
