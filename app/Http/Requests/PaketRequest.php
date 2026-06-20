<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->isMethod('post') ? 'paket.create' : 'paket.update';

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * Normalize the masked Rupiah input back to an integer before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('harga')) {
            $this->merge([
                'harga' => rupiah_clean((string) $this->input('harga')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'kecepatan_mbps' => ['nullable', 'integer', 'min:1'],
            'harga' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama paket wajib diisi.',
            'nama.max' => 'Nama paket maksimal 100 karakter.',
            'kecepatan_mbps.integer' => 'Kecepatan harus berupa angka.',
            'kecepatan_mbps.min' => 'Kecepatan minimal 1 Mbps.',
            'harga.required' => 'Harga wajib diisi.',
            'harga.integer' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validatedData(): array
    {
        $data = $this->validated();
        $data['is_active'] = $this->boolean('is_active');

        return $data;
    }
}
