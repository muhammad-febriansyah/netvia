<?php

namespace App\Http\Requests\Pengaturan;

class WhatsappSettingRequest extends PengaturanRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'wa_driver' => ['required', 'in:cloud_api,gateway,chatcepat,log'],
            'wa_nomor_pengirim' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wa_driver.required' => 'Driver WhatsApp wajib dipilih.',
            'wa_driver.in' => 'Driver WhatsApp tidak valid.',
        ];
    }
}
