<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    public const LOCALES = [
        'en' => 'EN',
        'ru' => 'RU',
        'uz' => 'UZ',
        'uz_cyrl' => 'УЗ',
        'tg' => 'ТЖК',
    ];

    protected $fillable = [
        'title',
        'subtitle',
        'subtitle_color',
        'gradient',
        'image',
        'images_by_locale',
        'image_class',
        'url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'images_by_locale' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function imageForLocale(string $locale = 'uz'): string
    {
        $map = is_array($this->images_by_locale) ? $this->images_by_locale : [];

        if (! empty($map[$locale])) {
            return $map[$locale];
        }

        if (! empty($map['uz'])) {
            return $map['uz'];
        }

        foreach (array_keys(self::LOCALES) as $loc) {
            if (! empty($map[$loc])) {
                return $map[$loc];
            }
        }

        return (string) $this->image;
    }

    public function allLocaleImages(): array
    {
        $map = is_array($this->images_by_locale) ? $this->images_by_locale : [];
        $result = [];

        foreach (array_keys(self::LOCALES) as $loc) {
            $result[$loc] = $map[$loc] ?? ($loc === 'uz' && empty($map) ? $this->image : null);
        }

        return $result;
    }

    public function toCatalogArray(string $locale = 'uz'): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'subtitle_color' => $this->subtitle_color,
            'gradient' => $this->gradient,
            'image' => $this->imageForLocale($locale),
            'images_by_locale' => $this->images_by_locale,
            'image_class' => $this->image_class,
            'url' => $this->url,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
