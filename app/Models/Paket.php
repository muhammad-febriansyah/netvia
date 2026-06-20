<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paket extends Model
{
    /** @use HasFactory<\Database\Factories\PaketFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama',
        'kecepatan_mbps',
        'harga',
        'deskripsi',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kecepatan_mbps' => 'integer',
            'harga' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Pelanggan, $this>
     */
    public function pelanggans(): HasMany
    {
        return $this->hasMany(Pelanggan::class);
    }
}
