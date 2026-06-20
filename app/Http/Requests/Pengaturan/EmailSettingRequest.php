<?php

namespace App\Http\Requests\Pengaturan;

class EmailSettingRequest extends PengaturanRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email_from_name' => ['nullable', 'string', 'max:100'],
            'email_from_address' => ['nullable', 'email', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email_from_address.email' => 'Format email tidak valid.',
        ];
    }
}
