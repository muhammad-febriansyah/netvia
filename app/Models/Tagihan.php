<?php

namespace App\Models;

use App\Enums\TagihanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    /** @use HasFactory<\Database\Factories\TagihanFactory> */
    use HasFactory;

    protected $fillable = [
        'nomor_tagihan',
        'pelanggan_id',
        'paket_id',
        'periode',
        'paket_nama',
        'harga',
        'jumlah',
        'tanggal_terbit',
        'tanggal_jatuh_tempo',
        'status',
        'paid_at',
        'public_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'periode' => 'date',
            'harga' => 'integer',
            'jumlah' => 'integer',
            'tanggal_terbit' => 'date',
            'tanggal_jatuh_tempo' => 'date',
            'paid_at' => 'datetime',
            'status' => TagihanStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    /**
     * @return BelongsTo<Paket, $this>
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    /**
     * @return HasMany<Pembayaran, $this>
     */
    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    /**
     * @return HasMany<NotifikasiLog, $this>
     */
    public function notifikasiLogs(): HasMany
    {
        return $this->hasMany(NotifikasiLog::class);
    }
}
