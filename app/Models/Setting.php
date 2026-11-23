<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'label',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::query()->where('key', $key)->first();

            return $setting ? $setting->value : $default;
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', ?string $label = null): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => (string) $value,
                'group' => $group,
                'label' => $label,
            ], fn ($v) => $v !== null)
        );
    }
}
