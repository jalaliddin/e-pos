<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Product;
use Telegram\Bot\Laravel\Facades\Telegram;

class OrderObserver
{
    public function updated(Order $order)
    {

        $total_price = number_format($order->total_price, 0, ',', ' ');
        $income_price = number_format($order->income_price, 0, ',', ' ');
        $message = "🛒 *Yangi buyurtma:*\n";
        $message .= "🆔 *Buyurtma ID:* {$order->id}\n";
        $message .= "---------------------------\n";
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            $productName = $product ? $product->name : $item->name;
            $standardPrice = $product ? $product->price : null;

            $line = "🔹 {$productName} — {$item->quantity} dona x " . number_format($item->price) . " so'm";

            if ($standardPrice !== null && (float)$item->price !== (float)$standardPrice) {
                $line .= "\n   ⚠️ _Narx o'zgartirildi:_ " . number_format($standardPrice) . " → " . number_format($item->price) . " so'm";
            }

            $message .= $line . "\n";
        }
        $message .= "---------------------------\n";
        $message .= "👤 *Mijoz:* {$order->customer->first_name} {$order->customer->phone}\n";
        $message .= "💰 *Narxi:* {$total_price} so'm\n";
        // $message .= "🏷️ *Asl narxi:* {$income_price} so'm\n";
        // $revenue = number_format($order->total_price - $order->income_price, 0, ',', ' ');
        // $message .= "📈 *Foyda:* {$revenue} so'm\n";


        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHAT_ID'),
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
    }
}
