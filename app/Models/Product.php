<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    public const LOCALES = [
        'en' => 'EN',
        'ru' => 'RU',
        'uz' => 'UZ',
        'uz_cyrl' => 'УЗ',
        'tg' => 'ТЖК',
    ];

    protected $fillable = [
        'category_id',
        'slug',
        'title',
        'full_title',
        'brand',
        'price_rub',
        'old_price_rub',
        'stock',
        'image',
        'gallery',
        'images',
        'specs',
        'spec_groups',
        'translations',
        'description',
        'warranty_text',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_rub' => 'integer',
            'old_price_rub' => 'integer',
            'stock' => 'integer',
            'images' => 'array',
            'specs' => 'array',
            'spec_groups' => 'array',
            'translations' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format((int) $this->price_rub, 0, '.', ' ').' ₽';
    }

    public function getFormattedOldPriceAttribute(): ?string
    {
        if (! $this->old_price_rub) {
            return null;
        }

        return number_format((int) $this->old_price_rub, 0, '.', ' ').' ₽';
    }

    public function translationFor(string $locale = 'uz'): array
    {
        $translations = is_array($this->translations) ? $this->translations : [];
        $locData = is_array($translations[$locale] ?? null) ? $translations[$locale] : [];
        $uzData = is_array($translations['uz'] ?? null) ? $translations['uz'] : [];

        return [
            'title' => $locData['title'] ?? ($locale === 'uz' ? $this->title : ($uzData['title'] ?? $this->title)),
            'full_title' => $locData['full_title'] ?? ($locale === 'uz' ? $this->full_title : ($uzData['full_title'] ?? $this->full_title)),
            'description' => $locData['description'] ?? ($locale === 'uz' ? $this->description : ($uzData['description'] ?? $this->description)),
            'warranty_text' => $locData['warranty_text'] ?? ($locale === 'uz' ? $this->warranty_text : ($uzData['warranty_text'] ?? $this->warranty_text)),
            'specs' => ! empty($locData['specs']) ? $locData['specs'] : ($locale === 'uz' ? $this->specs : ($uzData['specs'] ?? $this->specs)),
        ];
    }

    public function allTranslationsForForm(): array
    {
        $translations = is_array($this->translations) ? $this->translations : [];
        $result = [];

        foreach (array_keys(self::LOCALES) as $loc) {
            $locData = is_array($translations[$loc] ?? null) ? $translations[$loc] : [];
            $isBase = $loc === 'uz';

            $result[$loc] = [
                'title' => $locData['title'] ?? ($isBase ? (string) $this->title : ''),
                'full_title' => $locData['full_title'] ?? ($isBase ? (string) $this->full_title : ''),
                'description' => $locData['description'] ?? ($isBase ? (string) $this->description : ''),
                'warranty_text' => $locData['warranty_text'] ?? ($isBase ? (string) ($this->warranty_text ?: 'Rasmiy kafolat 12 oy · Original mahsulot') : ''),
                'specs' => is_array($locData['specs'] ?? null) ? $locData['specs'] : ($isBase && is_array($this->specs) ? $this->specs : []),
            ];
        }

        return $result;
    }

    public function toCatalogArray(string $locale = 'uz'): array
    {
        $gallery = $this->gallery ?: $this->image;
        $images = is_array($this->images) && count($this->images) > 0
            ? array_values($this->images)
            : [$gallery];

        $loc = $this->translationFor($locale);

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category?->slug ?? '',
            'category_id' => $this->category_id,
            'price' => $this->formatted_price,
            'price_rub' => $this->price_rub,
            'old_price_rub' => $this->old_price_rub,
            'stock' => $this->stock,
            'title' => $loc['title'] ?: $this->title,
            'full_title' => $loc['full_title'] ?: ($loc['title'] ?: ($this->full_title ?: $this->title)),
            'brand' => $this->brand,
            'image' => $this->image,
            'gallery' => $gallery,
            'images' => $images,
            'specs' => is_array($loc['specs']) && count($loc['specs']) > 0 ? $loc['specs'] : null,
            'spec_groups' => is_array($this->spec_groups) && count($this->spec_groups) > 0 ? $this->spec_groups : null,
            'description' => $loc['description'],
            'warranty_text' => $loc['warranty_text'] ?: $this->warranty_text,
            'translations' => $this->translations,
        ];
    }
}
