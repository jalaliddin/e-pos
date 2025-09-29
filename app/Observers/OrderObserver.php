<?php

namespace App\Observers;

use App\Models\Order;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderObserver
{
    public function saving(Order $order)
    {
        // dd($order);
        $message = "🛒 Yangi buyurtma:\n";
        $message .= "Buyurtma ID: {$order->id}\n";
        $message .= "Mijoz: {$order->customer->first_name}\n";
        $message .= "Narxi: {$order->total_price} so'm\n";
        $message .= "Asl narxi: {$order->income_price} so'm\n";
        $revenue = $order->total_price - $order->income_price;
        $message .= "Foyda: {$revenue} so'm\n";


        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
        ]);
    }
}
