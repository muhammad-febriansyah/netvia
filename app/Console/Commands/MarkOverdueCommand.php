<?php

namespace App\Console\Commands;

use App\Actions\Tagihan\MarkOverdueTagihanAction;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarkOverdueCommand extends Command
{
    protected $signature = 'billing:mark-overdue';

    protected $description = 'Mark unpaid tagihan past their due date as overdue.';

    public function handle(MarkOverdueTagihanAction $action): int
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();
        $affected = $action->execute($today);

        $this->info("Tagihan ditandai overdue: {$affected}.");

        return self::SUCCESS;
    }
}
