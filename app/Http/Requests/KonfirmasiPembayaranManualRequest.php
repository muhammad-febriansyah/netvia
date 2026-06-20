<?php

namespace App\Http\Requests;

use App\Enums\PembayaranMetode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class KonfirmasiPembayaranManualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pembayaran.konfirmasi') ?? false;
    }

    /**
     * Parse the masked Rupiah amount back to an integer before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('jumlah_bayar')) {
            $this->merge([
                'jumlah_bayar' => rupiah_clean((string) $this->input('jumlah_bayar')),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'metode' => ['required', (new Enum(PembayaranMetode::class))->only([
                PembayaranMetode::TransferManual,
                PembayaranMetode::Cash,
            ])],
            'jumlah_bayar' => ['required', 'integer', 'min:1'],
            'bukti_transfer' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function metode(): PembayaranMetode
    {
        return PembayaranMetode::from($this->string('metode')->value());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'metode.required' => 'Metode pembayaran wajib dipilih.',
            'jumlah_bayar.required' => 'Jumlah bayar wajib diisi.',
            'jumlah_bayar.integer' => 'Jumlah bayar harus berupa angka.',
            'jumlah_bayar.min' => 'Jumlah bayar harus lebih dari nol.',
            'bukti_transfer.file' => 'Bukti transfer harus berupa berkas.',
            'bukti_transfer.mimes' => 'Bukti transfer harus berformat JPG, PNG, atau PDF.',
            'bukti_transfer.max' => 'Ukuran bukti transfer maksimal 5 MB.',
        ];
    }
}
