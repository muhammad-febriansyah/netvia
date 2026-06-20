<?php

namespace App\Models;

use App\Enums\NotifikasiChannel;
use App\Enums\NotifikasiJenis;
use App\Enums\NotifikasiStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiLog extends Model
{
    /** @use HasFactory<\Database\Factories\NotifikasiLogFactory> */
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'pelanggan_id',
        'channel',
        'jenis',
        'status',
        'recipient',
        'payload',
        'error_message',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => NotifikasiChannel::class,
            'jenis' => NotifikasiJenis::class,
            'status' => NotifikasiStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Tagihan, $this>
     */
    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
