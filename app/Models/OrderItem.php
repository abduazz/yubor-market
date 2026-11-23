<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'title',
        'quantity',
        'qty_label',
        'unit_price_rub',
        'total_price_rub',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price_rub' => 'integer',
            'total_price_rub' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
