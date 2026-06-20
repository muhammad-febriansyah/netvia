<?php

namespace App\Http\Requests;

use App\Enums\PelangganStatus;
use App\Support\WhatsappNumber;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PelangganRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post') ? 'pelanggan.create' : 'pelanggan.update';

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * Normalize the WhatsApp number to `62xxx` before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('no_wa')) {
            $this->merge([
                'no_wa' => WhatsappNumber::normalize($this->input('no_wa')),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'no_wa' => ['required', 'string', 'regex:/^62\d{8,13}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'paket_id' => ['required', Rule::exists('pakets', 'id')->whereNull('deleted_at')],
            'tanggal_aktivasi' => ['required', 'date'],
            'tgl_jatuh_tempo' => ['required', 'integer', 'between:1,28'],
            'status' => ['required', Rule::enum(PelangganStatus::class)],
            'catatan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'paket_id.required' => 'Paket wajib dipilih.',
            'paket_id.exists' => 'Paket yang dipilih tidak valid.',
            'tanggal_aktivasi.required' => 'Tanggal aktivasi wajib diisi.',
            'tanggal_aktivasi.date' => 'Tanggal aktivasi tidak valid.',
            'tgl_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tgl_jatuh_tempo.between' => 'Tanggal jatuh tempo harus antara 1 sampai 28.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
