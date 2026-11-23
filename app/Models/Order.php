<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'status',
        'sender_name',
        'sender_phone',
        'recipient_name',
        'recipient_phone',
        'recipient_phone_2',
        'region',
        'district',
        'address',
        'landmark',
        'wish',
        'payment_method',
        'exchange_rate',
        'card_masked',
        'items_total_rub',
        'discount_rub',
        'oversize_fee_rub',
        'gift_wrap_fee_rub',
        'delivery_fee_rub',
        'total_rub',
        'courier_name',
        'courier_phone',
        'estimated_delivery_text',
        'status_note_title',
        'status_note_text',
        'admin_note',
        'delivered_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'float',
            'items_total_rub' => 'integer',
            'discount_rub' => 'integer',
            'oversize_fee_rub' => 'integer',
            'gift_wrap_fee_rub' => 'integer',
            'delivery_fee_rub' => 'integer',
            'total_rub' => 'integer',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function formatRub(int $amount): string
    {
        return number_format($amount, 0, '.', ' ').' ₽';
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->formatRub((int) $this->total_rub);
    }

    public function getTotalUzsAttribute(): int
    {
        return (int) round($this->total_rub * ($this->exchange_rate ?: 158));
    }

    public function getFormattedTotalUzsAttribute(): string
    {
        return number_format($this->total_uzs, 0, '.', ' ').' UZS';
    }

    public function toCatalogOrderArray(): array
    {
        return [
            'id' => $this->order_number,
            'items' => $this->items->map(fn (OrderItem $item) => [
                'title' => $item->title,
                'qty' => $item->qty_label ?: ($item->quantity.' dona'),
                'price' => number_format((int) $item->total_price_rub, 0, '.', ' ').' ₽',
                'image' => $item->image,
            ])->all(),
        ];
    }
}
