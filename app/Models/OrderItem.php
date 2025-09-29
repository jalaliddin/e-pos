<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $fillable =[
        'name',
        'income_price',
        'price',
        'tax',
        'quantity',
        'product_id',
        'order_id'
    ];
// public function getIncomePriceAttribute($value)
// {
//     return $value ?? 0;
// }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
