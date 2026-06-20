<?php

namespace App\Http\Requests\Pengaturan;

use Illuminate\Foundation\Http\FormRequest;

abstract class PengaturanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('pengaturan.update') ?? false;
    }

    /**
     * The validated values mapped to setting keys for persistence.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->validated();
    }
}
