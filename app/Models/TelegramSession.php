<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramSession extends Model
{
    use HasFactory;
    protected $fillable = [
        'chat_id', 
        'step', 
        'product_id', 
        'customer_name', 
        'customer_phone'
    ];
}
