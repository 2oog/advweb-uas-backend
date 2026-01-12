<?php

namespace App\Models;

use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'menu_name',
        'quantity',
        'price_at_time',
        'subtotal',
        'item_discount',
    ];

    protected $casts = [
        'item_discount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
