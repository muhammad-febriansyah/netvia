<?php

namespace App\Actions\Tagihan;

use App\Repositories\TagihanRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MarkOverdueTagihanAction
{
    public function __construct(private TagihanRepository $tagihans) {}

    /**
     * Flag unpaid tagihan past due as overdue. Returns rows affected.
     */
    public function execute(Carbon $today): int
    {
        return DB::transaction(fn (): int => $this->tagihans->markOverdue($today));
    }
}
