<?php

namespace App\Http\Controllers;

use App\Actions\Pemutusan\AjukanPemutusanAction;
use App\Enums\PelangganStatus;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Repositories\PelangganRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PortalController extends Controller
{
    public function __construct(private PelangganRepository $pelanggans) {}

    public function dashboard(Request $request): View
    {
        $pelanggan = $this->pelanggan($request);
        $pelanggan->load('paket');

        return view('portal.dashboard', [
            'pelanggan' => $pelanggan,
            'ringkasan' => $this->pelanggans->ringkasanTagihan($pelanggan),
        ]);
    }

    public function langganan(Request $request): View
    {
        $pelanggan = $this->pelanggan($request);
        $pelanggan->load('paket');

        return view('portal.langganan', [
            'pelanggan' => $pelanggan,
            'pakets' => Paket::query()->where('is_active', true)->orderBy('nama')->get(),
        ]);
    }

    public function updateLangganan(Request $request): RedirectResponse
    {
        $pelanggan = $this->pelanggan($request);

        $data = $request->validate([
            'paket_id' => ['required', Rule::exists('pakets', 'id')->where('is_active', true)],
        ], [
            'paket_id.required' => 'Paket wajib dipilih.',
            'paket_id.exists' => 'Paket tidak tersedia.',
        ]);

        $pelanggan->update(['paket_id' => $data['paket_id']]);

        return back()->with('success', 'Paket berhasil diubah. Berlaku pada periode tagihan berikutnya.');
    }

    public function riwayat(Request $request): View
    {
        $pelanggan = $this->pelanggan($request);

        $tagihans = $pelanggan->tagihans()
            ->with('pembayarans')
            ->latest('periode')
            ->paginate(10);

        return view('portal.riwayat', compact('pelanggan', 'tagihans'));
    }

    public function pemutusan(Request $request): View
    {
        $pelanggan = $this->pelanggan($request);

        return view('portal.pemutusan', [
            'pelanggan' => $pelanggan,
            'pengajuan' => $pelanggan->pemutusanLangganans()->latest()->get(),
            'canRequest' => $pelanggan->status === PelangganStatus::Aktif,
        ]);
    }

    public function storePemutusan(Request $request, AjukanPemutusanAction $action): RedirectResponse
    {
        $pelanggan = $this->pelanggan($request);

        if ($pelanggan->status !== PelangganStatus::Aktif) {
            return back()->with('error', 'Pengajuan pemutusan hanya untuk langganan aktif.');
        }

        $data = $request->validate([
            'alasan' => ['required', 'string', 'max:500'],
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'alasan.required' => 'Alasan pemutusan wajib diisi.',
            'foto.required' => 'Foto wajib dilampirkan.',
            'foto.image' => 'Lampiran harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $fotoPath = $request->file('foto')->store('pemutusan', 'public');

        try {
            $action->execute($pelanggan, $data['alasan'], $fotoPath);
        } catch (RuntimeException $e) {
            Storage::disk('public')->delete($fotoPath);

            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan pemutusan terkirim. Menunggu persetujuan admin.');
    }

    private function pelanggan(Request $request): Pelanggan
    {
        $pelanggan = $request->user()->pelanggan;

        abort_if($pelanggan === null, 403, 'Akun tidak terhubung ke data pelanggan.');

        return $pelanggan;
    }
}
