<?php

namespace App\Actions\Pemutusan;

use App\Enums\PelangganStatus;
use App\Enums\PemutusanStatus;
use App\Models\PemutusanLangganan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProsesPemutusanAction
{
    /**
     * Admin approves or rejects a termination request. Approval deactivates the
     * pelanggan (administrative marker only — no network action).
     *
     * @throws RuntimeException when the request is already processed.
     */
    public function execute(PemutusanLangganan $pemutusan, PemutusanStatus $decision, User $admin, ?string $catatan = null): PemutusanLangganan
    {
        if ($pemutusan->status !== PemutusanStatus::Pending) {
            throw new RuntimeException('Pengajuan ini sudah diproses.');
        }

        return DB::transaction(function () use ($pemutusan, $decision, $admin, $catatan): PemutusanLangganan {
            $pemutusan->update([
                'status' => $decision,
                'catatan_admin' => $catatan,
                'diproses_by' => $admin->id,
                'diproses_at' => Carbon::now(),
            ]);

            if ($decision === PemutusanStatus::Approved) {
                $pemutusan->pelanggan->update(['status' => PelangganStatus::Nonaktif]);
            }

            return $pemutusan;
        });
    }
}
