<?php

namespace App\Observers;

use App\Models\Order;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderObserver
{
    public function updated(Order $order)
    {

        $total_price = number_format($order->total_price,0,',',' ');
        $income_price = number_format($order->income_price,0,',',' ');
        $message = "🛒 *Yangi buyurtma:*\n";
        $message .= "🆔 *Buyurtma ID:* {$order->id}\n";
        $message .= "👤 *Mijoz:* {$order->customer->first_name}\n";
        $message .= "💰 *Narxi:* {$total_price} so'm\n";
        $message .= "🏷️ *Asl narxi:* {$income_price} so'm\n";
        $revenue = number_format($order->total_price - $order->income_price,0,',',' ');
        $message .= "📈 *Foyda:* {$revenue} so'm\n";


        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}