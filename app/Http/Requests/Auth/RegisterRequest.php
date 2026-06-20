<?php

namespace App\Http\Requests\Auth;

use App\Support\WhatsappNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('no_wa')) {
            $this->merge(['no_wa' => WhatsappNumber::normalize($this->input('no_wa'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'no_wa' => ['required', 'string', 'regex:/^62\d{8,13}$/'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'paket_id' => ['required', Rule::exists('pakets', 'id')->where('is_active', true)],
            'tgl_jatuh_tempo' => ['required', 'integer', 'between:1,28'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid.',
            'paket_id.required' => 'Paket wajib dipilih.',
            'paket_id.exists' => 'Paket tidak tersedia.',
            'tgl_jatuh_tempo.required' => 'Tanggal jatuh tempo wajib diisi.',
            'tgl_jatuh_tempo.between' => 'Tanggal jatuh tempo antara 1–28.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function registrationData(): array
    {
        return [
            'name' => $this->string('name')->value(),
            'email' => $this->string('email')->value(),
            'password' => $this->string('password')->value(),
            'no_wa' => $this->string('no_wa')->value(),
            'alamat' => $this->input('alamat'),
            'paket_id' => $this->integer('paket_id'),
            'tgl_jatuh_tempo' => $this->integer('tgl_jatuh_tempo'),
        ];
    }
}
