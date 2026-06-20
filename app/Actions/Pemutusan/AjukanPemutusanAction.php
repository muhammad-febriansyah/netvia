<?php

namespace App\Actions\Pemutusan;

use App\Models\Pelanggan;
use App\Models\PemutusanLangganan;
use App\Repositories\PemutusanLanggananRepository;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AjukanPemutusanAction
{
    public function __construct(private PemutusanLanggananRepository $pemutusan) {}

    /**
     * Customer submits a termination request with reason and photo path.
     *
     * @throws RuntimeException when a pending request already exists.
     */
    public function execute(Pelanggan $pelanggan, string $alasan, string $fotoPath): PemutusanLangganan
    {
        if ($this->pemutusan->hasPending($pelanggan->id)) {
            throw new RuntimeException('Anda sudah punya pengajuan pemutusan yang menunggu diproses.');
        }

        return DB::transaction(fn (): PemutusanLangganan => $this->pemutusan->create([
            'pelanggan_id' => $pelanggan->id,
            'alasan' => $alasan,
            'foto' => $fotoPath,
            'status' => 'pending',
        ]));
    }
}
