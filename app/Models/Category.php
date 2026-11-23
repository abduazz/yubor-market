<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'section',
        'image',
        'demo_count',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'demo_count' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class)->orderBy('sort_order')->orderBy('id');
    }

    public function toCatalogArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'section' => $this->section,
            'image' => $this->image,
            'demo_count' => $this->demo_count,
        ];
    }
}
