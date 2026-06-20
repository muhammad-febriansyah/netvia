<?php

namespace App\Http\Requests\Pengaturan;

class PembayaranSettingRequest extends PengaturanRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'qris_aktif' => $this->boolean('qris_aktif') ? '1' : '0',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'qris_aktif' => ['required', 'in:0,1'],
            'bank_nama' => ['nullable', 'string', 'max:50'],
            'bank_no_rekening' => ['nullable', 'string', 'max:50'],
            'bank_atas_nama' => ['nullable', 'string', 'max:100'],
        ];
    }
}
