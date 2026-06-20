<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MessageTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('notifikasi.template') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active') ? '1' : '0',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string'],
            'is_active' => ['required', 'in:0,1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'Isi pesan wajib diisi.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function templateData(): array
    {
        return [
            'subject' => $this->input('subject'),
            'body' => $this->input('body'),
            'is_active' => $this->boolean('is_active'),
        ];
    }
}
