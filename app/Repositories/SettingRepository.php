<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository
{
    public function get(string $key, ?string $default = null): ?string
    {
        return Setting::query()->where('key', $key)->value('value') ?? $default;
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key);

        return $value === null ? $default : (int) $value;
    }

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null ? $default : in_array($value, ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * All settings as a key => value map.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Setting::query()->pluck('value', 'key')->all();
    }

    public function set(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Upsert many settings at once.
     *
     * @param  array<string, mixed>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value === null ? null : (string) $value);
        }
    }
}
