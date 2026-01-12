<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'table_number',
        'tax_percent',
        'global_discount',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'tax_percent' => 'decimal:2',
        'global_discount' => 'decimal:2',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
