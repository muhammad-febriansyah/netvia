<?php

namespace App\Actions\Tagihan;

use App\Enums\TagihanStatus;
use App\Exceptions\TagihanNotVoidableException;
use App\Models\Tagihan;
use Illuminate\Support\Facades\DB;

class VoidTagihanAction
{
    /**
     * Cancel an unpaid/overdue tagihan with a reason. Paid or already-void
     * tagihan cannot be voided.
     *
     * @throws TagihanNotVoidableException
     */
    public function execute(Tagihan $tagihan, string $reason): Tagihan
    {
        if ($tagihan->status === TagihanStatus::Paid) {
            throw TagihanNotVoidableException::alreadyPaid();
        }

        if ($tagihan->status === TagihanStatus::Void) {
            throw TagihanNotVoidableException::alreadyVoid();
        }

        return DB::transaction(function () use ($tagihan, $reason): Tagihan {
            $tagihan->update([
                'status' => TagihanStatus::Void,
                'void_reason' => $reason,
            ]);

            return $tagihan;
        });
    }
}
