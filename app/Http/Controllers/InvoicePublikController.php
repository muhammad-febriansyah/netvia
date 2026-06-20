<?php

namespace App\Http\Controllers;

use App\Actions\Pembayaran\BuatPembayaranQrisAction;
use App\Enums\PembayaranStatus;
use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotPayableException;
use App\Models\Tagihan;
use App\Repositories\PembayaranRepository;
use App\Repositories\SettingRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class InvoicePublikController extends Controller
{
    public function __construct(
        private PembayaranRepository $pembayarans,
        private SettingRepository $settings,
    ) {}

    /**
     * Public, token-addressed invoice page for customers (no account needed).
     *
     * When the tagihan is payable and QRIS is enabled, a QRIS payment is opened
     * server-side (idempotently — an unexpired one is reused) so the customer
     * gets a pay link without needing to authenticate.
     */
    public function invoice(string $token, BuatPembayaranQrisAction $buatQris): View
    {
        $tagihan = $this->resolveTagihan($token);
        $qrisAktif = $this->settings->getBool('qris_aktif', true);
        $pembayaran = null;

        $payable = in_array($tagihan->status, [TagihanStatus::Unpaid, TagihanStatus::Overdue], true);

        if ($payable && $qrisAktif) {
            try {
                $pembayaran = $buatQris->execute($tagihan);
            } catch (TagihanNotPayableException) {
                $pembayaran = null;
            }
        }

        return view('invoice-publik.show', [
            'tagihan' => $tagihan,
            'perusahaan' => $this->profilPerusahaan(),
            'pembayaran' => $pembayaran,
            'qrisAktif' => $qrisAktif,
        ]);
    }

    /**
     * Downloadable paid-receipt PDF; only available once the tagihan is paid.
     */
    public function struk(string $token): SymfonyResponse
    {
        $tagihan = $this->resolveTagihan($token);

        if ($tagihan->status !== TagihanStatus::Paid) {
            abort(Response::HTTP_FORBIDDEN, 'Struk hanya tersedia untuk tagihan yang sudah lunas.');
        }

        $pembayaran = $tagihan->pembayarans()
            ->where('status', PembayaranStatus::Success)
            ->latest('dibayar_at')
            ->first();

        $pdf = Pdf::loadView('invoice-publik.struk', [
            'tagihan' => $tagihan,
            'perusahaan' => $this->profilPerusahaan(),
            'pembayaran' => $pembayaran,
        ]);

        $filename = str_replace('/', '-', $tagihan->nomor_tagihan);

        return $pdf->download("struk-{$filename}.pdf");
    }

    private function resolveTagihan(string $token): Tagihan
    {
        return Tagihan::query()
            ->with('pelanggan')
            ->where('public_token', $token)
            ->firstOrFail();
    }

    /**
     * @return array<string, string|null>
     */
    private function profilPerusahaan(): array
    {
        return [
            'nama' => $this->settings->get('nama_perusahaan'),
            'logo' => $this->settings->get('logo'),
            'alamat' => $this->settings->get('alamat'),
            'no_telp' => $this->settings->get('no_telp'),
            'no_wa_cs' => $this->settings->get('no_wa_cs'),
            'email_cs' => $this->settings->get('email_cs'),
            'footer_invoice' => $this->settings->get('footer_invoice'),
            'bank_nama' => $this->settings->get('bank_nama'),
            'bank_no_rekening' => $this->settings->get('bank_no_rekening'),
            'bank_atas_nama' => $this->settings->get('bank_atas_nama'),
        ];
    }
}
