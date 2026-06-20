<?php

namespace App\Http\Requests\Pengaturan;

class BillingSettingRequest extends PengaturanRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'kirim_invoice_baru' => $this->boolean('kirim_invoice_baru') ? '1' : '0',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'generate_hari_sebelum_jatuh_tempo' => ['required', 'integer', 'between:0,28'],
            'reminder_overdue_hari' => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
            'kirim_invoice_baru' => ['required', 'in:0,1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'generate_hari_sebelum_jatuh_tempo.required' => 'Jumlah hari wajib diisi.',
            'generate_hari_sebelum_jatuh_tempo.integer' => 'Jumlah hari harus berupa angka.',
            'generate_hari_sebelum_jatuh_tempo.between' => 'Jumlah hari antara 0 sampai 28.',
            'reminder_overdue_hari.regex' => 'Format hari reminder harus angka dipisah koma, mis. 1,3,7.',
        ];
    }
}
