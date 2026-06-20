<?php

namespace App\Models;

use App\Enums\PelangganStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelanggan extends Model
{
    /** @use HasFactory<\Database\Factories\PelangganFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'no_wa',
        'email',
        'alamat',
        'paket_id',
        'tanggal_aktivasi',
        'tgl_jatuh_tempo',
        'status',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_aktivasi' => 'date',
            'tgl_jatuh_tempo' => 'integer',
            'status' => PelangganStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Paket, $this>
     */
    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    /**
     * @return HasMany<Tagihan, $this>
     */
    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    /**
     * @return HasMany<NotifikasiLog, $this>
     */
    public function notifikasiLogs(): HasMany
    {
        return $this->hasMany(NotifikasiLog::class);
    }
}
