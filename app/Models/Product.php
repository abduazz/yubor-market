<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
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

    public function toCatalogArray(): array
    {
        $gallery = $this->gallery ?: $this->image;
        $images = is_array($this->images) && count($this->images) > 0
            ? array_values($this->images)
            : [$gallery];

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'category' => $this->category?->slug ?? '',
            'category_id' => $this->category_id,
            'price' => $this->formatted_price,
            'price_rub' => $this->price_rub,
            'old_price_rub' => $this->old_price_rub,
            'stock' => $this->stock,
            'title' => $this->title,
            'full_title' => $this->full_title ?: $this->title,
            'brand' => $this->brand,
            'image' => $this->image,
            'gallery' => $gallery,
            'images' => $images,
            'specs' => is_array($this->specs) && count($this->specs) > 0 ? $this->specs : null,
            'spec_groups' => is_array($this->spec_groups) && count($this->spec_groups) > 0 ? $this->spec_groups : null,
            'description' => $this->description,
            'warranty_text' => $this->warranty_text,
        ];
    }
}
