<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidTagihanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tagihan.void') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alasan' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alasan.required' => 'Alasan pembatalan wajib diisi.',
            'alasan.max' => 'Alasan maksimal 255 karakter.',
        ];
    }
}
