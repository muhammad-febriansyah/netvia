<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateManualTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tagihan.generate') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pelanggan_id' => ['required', 'exists:pelanggans,id'],
            'periode' => ['nullable', 'date_format:Y-m'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_id.exists' => 'Pelanggan tidak ditemukan.',
            'periode.date_format' => 'Format periode harus YYYY-MM.',
        ];
    }
}
