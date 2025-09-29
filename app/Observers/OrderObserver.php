<?php

namespace App\Observers;

use App\Models\Order;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderObserver
{
    public function updated(Order $order)
    {
        // dd($order);
        $message = "🛒 Yangi buyurtma:\n";
        $message .= "Buyurtma ID: {$order->id}\n";
        $message .= "Ism: {$order->customer->first_name}\n";
        $message .= "Narxi: {$order->total_price} so'm\n";

        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
        ]);
    }
}
