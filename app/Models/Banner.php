<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'subtitle_color',
        'gradient',
        'image',
        'image_class',
        'url',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function toCatalogArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'subtitle_color' => $this->subtitle_color,
            'gradient' => $this->gradient,
            'image' => $this->image,
            'image_class' => $this->image_class,
            'url' => $this->url,
        ], fn ($value) => $value !== null && $value !== '');
    }
}
