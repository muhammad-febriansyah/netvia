<?php

namespace App\Console\Commands;

use App\Enums\NotifikasiJenis;
use App\Enums\TagihanStatus;
use App\Models\Setting;
use App\Models\Tagihan;
use App\Services\NotifikasiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class KirimReminderCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'billing:remind';

    /**
     * @var string
     */
    protected $description = 'Kirim reminder tagihan (H-3, jatuh tempo, lewat tempo) via WhatsApp & Email';

    /**
     * Execute the console command.
     */
    public function handle(NotifikasiService $notifikasi): int
    {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $h3 = $this->remind(
            $notifikasi,
            NotifikasiJenis::ReminderH3,
            [$today->copy()->addDays(config('notifikasi.reminder.h3_days_before', 3))],
            [TagihanStatus::Unpaid],
        );

        $due = $this->remind(
            $notifikasi,
            NotifikasiJenis::ReminderDue,
            [$today->copy()],
            [TagihanStatus::Unpaid],
        );

        $overdue = $this->remind(
            $notifikasi,
            NotifikasiJenis::ReminderOverdue,
            collect($this->overdueDays())->map(fn (int $d) => $today->copy()->subDays($d))->all(),
            [TagihanStatus::Unpaid, TagihanStatus::Overdue],
        );

        $this->info("Reminder diproses — H-3: {$h3}, jatuh tempo: {$due}, lewat tempo: {$overdue}.");

        return self::SUCCESS;
    }

    /**
     * Queue reminders for bills whose due date matches any of the given dates.
     *
     * @param  list<Carbon>  $dates
     * @param  list<TagihanStatus>  $statuses
     */
    protected function remind(NotifikasiService $notifikasi, NotifikasiJenis $jenis, array $dates, array $statuses): int
    {
        $count = 0;

        Tagihan::query()
            ->with('pelanggan')
            ->whereIn('status', array_map(fn (TagihanStatus $s) => $s->value, $statuses))
            ->where(function ($query) use ($dates): void {
                foreach ($dates as $date) {
                    $query->orWhereDate('tanggal_jatuh_tempo', $date->toDateString());
                }
            })
            ->each(function (Tagihan $tagihan) use ($notifikasi, $jenis, &$count): void {
                $notifikasi->kirim($tagihan, $jenis);
                $count++;
            });

        return $count;
    }

    /**
     * Overdue offsets (days after due date) from settings, falling back to config.
     *
     * @return list<int>
     */
    protected function overdueDays(): array
    {
        $configured = Setting::getValue('reminder_overdue_hari');

        if ($configured === null || trim($configured) === '') {
            return config('notifikasi.reminder.overdue_days_after', [1, 3, 7]);
        }

        return collect(explode(',', $configured))
            ->map(fn (string $d) => (int) trim($d))
            ->filter(fn (int $d) => $d > 0)
            ->values()
            ->all();
    }
}
