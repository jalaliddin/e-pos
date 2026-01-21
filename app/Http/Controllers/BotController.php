<?php

namespace App\Http\Controllers;

use App\Livewire\Order\Cart as OrderCart;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\TelegramSession;
use Illuminate\Http\Request;
// use App\Models\Cart;

class BotController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::getWebhookUpdate();
        $chatId = $update->getChat()->getId();
        $firstName = $update->getMessage()->getFrom()->getFirstName();

        // 1. Faqat xodimlarga ruxsat
        $allowedStaff = explode(',', env('ALLOWED_STAFF_IDS'));
        if (!in_array($chatId, $allowedStaff)) {
            return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Ruxsat berilmagan!"]);
        }

        $session = TelegramSession::firstOrCreate(['chat_id' => $chatId]);

        // 2. Start buyrug'i
        if ($update->has('message') && $update->getMessage()->getText() == '/start') {
            $session->update(['step' => 'start', 'product_id' => null]);
            return $this->sendCategories($chatId);
        }

        // 3. Tugmalar bosilganda (Callback)
        if ($update->has('callback_query')) {
            $data = $update->getCallbackQuery()->getData();

            if (str_starts_with($data, 'cat_')) {
                return $this->sendProducts($chatId, str_replace('cat_', '', $data));
            }

            if (str_starts_with($data, 'prod_')) {
                $session->update(['product_id' => str_replace('prod_', '', $data), 'step' => 'wait_name']);
                return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "👤 Mijoz ismini yozing:"]);
            }
        }

        // 4. Ma'lumotlarni yig'ish (Text input)
        if ($update->has('message')) {
            $text = $update->getMessage()->getText();

            switch ($session->step) {
                case 'wait_name':
                    $session->update(['customer_name' => $text, 'step' => 'wait_phone']);
                    return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "📞 Telefon raqami:"]);

                case 'wait_phone':
                    $session->update(['customer_phone' => $text, 'step' => 'wait_amount']);
                    return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "💰 Sotuv summasini kiriting:"]);

                case 'wait_amount':
                    if (!is_numeric($text)) {
                        return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Iltimos, summani faqat raqamlarda kiriting!"]);
                    }

                    // ASOSIY ORDER JADVALIGA SAQLASH
                    // $order = Order::create([
                    //     'product_id'     => $session->product_id,
                    //     'customer_name'  => $session->customer_name,
                    //     'customer_phone' => $session->customer_phone,
                    //     'amount'         => $text,
                    //     'status'         => 'completed'
                    // ]);
                    
                    // $staffIdentifier = "TG." . $chatId . "." . $firstName;
                    
                    $customer = Customer::create([
                        'first_name' => $session->customer_name,
                        'last_name' => 'staffIdentifier',
                        'phone' => $session->customer_phone
                    ]);

                    $orderCart = new OrderCart();

                    $order = $orderCart->botCheckout($session->product_id, $customer->id, $text);

                    $session->update(['step' => 'start']); // Sessiyani tozalash

                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "✅ Order Complete!\nID: #{$order->id}\nMijoz: {$customer->first_name}\nSumma: " . number_format($text) . " so'm\n\nYangi order uchun /start bosing."
                    ]);
                    return response()->json('success',200);
            }
        }
    }

    private function sendCategories($chatId)
    {
        $categories = Category::all();
        $buttons = $categories->map(fn($c) => [['text' => "📁 " . $c->name, 'callback_data' => 'cat_' . $c->id]])->toArray();
        return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Kategoriyani tanlang:", 'reply_markup' => json_encode(['inline_keyboard' => $buttons])]);
    }

    private function sendProducts($chatId, $catId)
    {
        $products = Product::where('category_id', $catId)->get();
        if ($products->isEmpty()) {
            return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Bu kategoriyada tovar yo'q."]);
        }
        $buttons = $products->map(fn($p) => [['text' => "📦 " . $p->name, 'callback_data' => 'prod_' . $p->id]])->toArray();
        return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Mahsulotni tanlang:", 'reply_markup' => json_encode(['inline_keyboard' => $buttons])]);
    }
}
