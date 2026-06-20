<?php

namespace App\Http\Controllers;

use App\Actions\Pengaturan\UpdateSettingsAction;
use App\Http\Requests\Pengaturan\BillingSettingRequest;
use App\Http\Requests\Pengaturan\EmailSettingRequest;
use App\Http\Requests\Pengaturan\PembayaranSettingRequest;
use App\Http\Requests\Pengaturan\ProfilSettingRequest;
use App\Http\Requests\Pengaturan\WhatsappSettingRequest;
use App\Mail\NotifikasiMail;
use App\Repositories\SettingRepository;
use App\Services\Whatsapp\WhatsappService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function __construct(
        private SettingRepository $settings,
        private UpdateSettingsAction $updateSettings,
    ) {}

    public function index(): View
    {
        return view('pengaturan.index', [
            'settings' => $this->settings->all(),
            'callbackUrl' => route('pembayaran.webhook'),
        ]);
    }

    public function updateProfil(ProfilSettingRequest $request): RedirectResponse
    {
        $values = $request->settings();

        if ($request->hasFile('logo')) {
            $old = $this->settings->get('logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $values['logo'] = $request->file('logo')->store('logo', 'public');
        }

        $this->updateSettings->execute($values);

        return back()->with('success', 'Profil perusahaan berhasil disimpan.');
    }

    public function updateBilling(BillingSettingRequest $request): RedirectResponse
    {
        $this->updateSettings->execute($request->settings());

        return back()->with('success', 'Parameter billing berhasil disimpan.');
    }

    public function updatePembayaran(PembayaranSettingRequest $request): RedirectResponse
    {
        $this->updateSettings->execute($request->settings());

        return back()->with('success', 'Konfigurasi pembayaran berhasil disimpan.');
    }

    public function updateWhatsapp(WhatsappSettingRequest $request): RedirectResponse
    {
        $this->updateSettings->execute($request->settings());

        return back()->with('success', 'Konfigurasi WhatsApp berhasil disimpan.');
    }

    public function updateEmail(EmailSettingRequest $request): RedirectResponse
    {
        $this->updateSettings->execute($request->settings());

        return back()->with('success', 'Konfigurasi email berhasil disimpan.');
    }

    public function tesWhatsapp(Request $request, WhatsappService $whatsapp): JsonResponse
    {
        $data = $request->validate([
            'no_wa' => ['required', 'string'],
        ], [
            'no_wa.required' => 'Nomor WA tujuan wajib diisi.',
        ]);

        $to = '62'.ltrim(preg_replace('/\D/', '', $data['no_wa']), '0');
        $result = $whatsapp->send($to, 'Tes koneksi WhatsApp dari '.config('app.name').' berhasil.');

        return response()->json([
            'success' => $result->success,
            'message' => $result->success ? 'Pesan tes terkirim ke '.$to.'.' : ($result->error ?? 'Gagal mengirim pesan tes.'),
        ], $result->success ? 200 : 422);
    }

    public function tesEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email tujuan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        try {
            Mail::to($data['email'])->send(new NotifikasiMail(
                'Tes Email '.config('app.name'),
                'Tes koneksi email dari '.config('app.name').' berhasil.',
            ));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email tes: '.$e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email tes terkirim ke '.$data['email'].'.',
        ]);
    }
}
