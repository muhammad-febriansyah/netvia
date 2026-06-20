<?php

namespace App\Models;

use App\Enums\PembayaranMetode;
use App\Enums\PembayaranStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    /** @use HasFactory<\Database\Factories\PembayaranFactory> */
    use HasFactory;

    protected $fillable = [
        'tagihan_id',
        'metode',
        'jumlah_bayar',
        'status',
        'pakasir_order_id',
        'pakasir_reference',
        'qr_string',
        'payment_url',
        'expired_at',
        'bukti_transfer',
        'dikonfirmasi_by',
        'dibayar_at',
        'raw_callback',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah_bayar' => 'integer',
            'metode' => PembayaranMetode::class,
            'status' => PembayaranStatus::class,
            'expired_at' => 'datetime',
            'dibayar_at' => 'datetime',
            'raw_callback' => 'array',
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
     * @return BelongsTo<User, $this>
     */
    public function dikonfirmasiBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_by');
    }
}
