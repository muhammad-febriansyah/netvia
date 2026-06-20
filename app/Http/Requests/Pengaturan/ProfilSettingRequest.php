<?php

namespace App\Http\Requests\Pengaturan;

class ProfilSettingRequest extends PengaturanRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_perusahaan' => ['required', 'string', 'max:100'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:30'],
            'no_wa_cs' => ['nullable', 'string', 'max:30'],
            'email_cs' => ['nullable', 'email', 'max:100'],
            'footer_invoice' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
            'email_cs.email' => 'Format email tidak valid.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.max' => 'Ukuran logo maksimal 1 MB.',
        ];
    }

    /**
     * Logo is handled as a file upload by the controller, not a plain setting.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return collect($this->validated())->except('logo')->all();
    }
}
