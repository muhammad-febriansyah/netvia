<?php

namespace App\Actions\Pengaturan;

use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\DB;

class UpdateSettingsAction
{
    public function __construct(private SettingRepository $settings) {}

    /**
     * @param  array<string, mixed>  $values
     */
    public function execute(array $values): void
    {
        DB::transaction(fn () => $this->settings->setMany($values));
    }
}
