<?php

namespace App\Models;

use App\Enums\PemutusanStatus;
use Database\Factories\PemutusanLanggananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemutusanLangganan extends Model
{
    /** @use HasFactory<PemutusanLanggananFactory> */
    use HasFactory;

    protected $fillable = [
        'pelanggan_id',
        'alasan',
        'foto',
        'status',
        'catatan_admin',
        'diproses_by',
        'diproses_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PemutusanStatus::class,
            'diproses_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function diprosesBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_by');
    }
}
