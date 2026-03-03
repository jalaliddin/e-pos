<?php

namespace App\Http\Controllers;

use App\Livewire\Cart;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use App\Models\Customer;
use App\Models\Product;
use App\Models\TelegramSession;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function handle(Request $request)
    {
        $update = Telegram::getWebhookUpdate();
        $chatId = $update->getChat()->getId();
        // $firstName = null;
        // $firstName = $update->getMessage()->getFrom()?->getFirstName() ?? '';
        // $firstName = $update->getFrom()->getFirstName();

        // 1. Faqat xodimlarga ruxsat
        $allowedStaff = explode(',', env('ALLOWED_STAFF_IDS'));
        if (!in_array($chatId, $allowedStaff)) {
            return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Ruxsat berilmagan!"]);
        }

        $session = TelegramSession::firstOrCreate(['chat_id' => $chatId]);

        // 2. Start buyrug'i
        if ($update->has('message') && $update->getMessage()->getText() == '/start') {
            $session->update([
                'step' => 'start',
                'product_id' => null,
                'customer_name' => null,
                'customer_phone' => null,
            ]);

            Cache::forget($this->selectedProductsCacheKey($chatId));

            return $this->sendCategories($chatId);
        }

        // 3. Tugmalar bosilganda (Callback)
        if ($update->has('callback_query')) {
            $data = $update->getCallbackQuery()->getData();

            if (str_starts_with($data, 'cat_')) {
                return $this->sendProducts($chatId, str_replace('cat_', '', $data));
            }

            if (str_starts_with($data, 'prod_')) {
                $productId = (int) str_replace('prod_', '', $data);

                $this->addProductToSelection($chatId, $productId);

                $product = Product::find($productId);

                $text = "🛒 " . ($product?->name ?? 'Mahsulot') . " savatga qo'shildi.\n\nYana qo'shishni xohlaysizmi?";

                $buttons = [
                    [
                        ['text' => "➕ Yana qo'shish", 'callback_data' => 'add_more'],
                        ['text' => "✅ Tanlandi", 'callback_data' => 'done_select'],
                    ],
                ];

                return Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'reply_markup' => json_encode(['inline_keyboard' => $buttons]),
                ]);
            }

            if ($data === 'add_more') {
                return $this->sendCategories($chatId);
            }

            if ($data === 'done_select') {
                $selected = Cache::get($this->selectedProductsCacheKey($chatId), []);

                if (empty($selected)) {
                    return Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Avval mahsulot tanlang. /start buyrug'ini bosib qaytadan urinib ko'ring.",
                    ]);
                }

                $session->update(['step' => 'wait_name']);

                return Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "👤 Mijoz ismini yozing:",
                ]);
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

                    $selectedProducts = Cache::get($this->selectedProductsCacheKey($chatId), []);

                    if (empty($selectedProducts)) {
                        return Telegram::sendMessage([
                            'chat_id' => $chatId,
                            'text' => "Mahsulotlar tanlanmagan. /start buyrug'ini bosib qaytadan urinib ko'ring.",
                        ]);
                    }

                    $customer = Customer::create([
                        'first_name' => $session->customer_name,
                        'last_name' => $chatId,
                        'phone' => $session->customer_phone,
                    ]);

                    $orderCart = new Cart();

                    $order = $orderCart->botCheckout($selectedProducts, $customer->id, $text);

                    Cache::forget($this->selectedProductsCacheKey($chatId));

                    $session->update([
                        'step' => 'start',
                        'product_id' => null,
                        'customer_name' => null,
                        'customer_phone' => null,
                    ]); // Sessiyani tozalash

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
        $products = Product::where('category_id', $catId)
            ->where('quantity', '>', 0)
            ->where('status', true)
            ->get();
        if ($products->isEmpty()) {
            return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Bu kategoriyada tovar yo'q."]);
        }
        $buttons = $products->map(fn($p) => [['text' => "📦 " . $p->name . ' - ' . $p->quantity . ' ta', 'callback_data' => 'prod_' . $p->id]])->toArray();
        return Telegram::sendMessage(['chat_id' => $chatId, 'text' => "Mahsulotni tanlang:", 'reply_markup' => json_encode(['inline_keyboard' => $buttons])]);
    }

    private function selectedProductsCacheKey($chatId): string
    {
        return 'tg_selected_products_' . $chatId;
    }

    private function addProductToSelection($chatId, int $productId): void
    {
        $key = $this->selectedProductsCacheKey($chatId);
        $selected = Cache::get($key, []);

        $selected[] = $productId;

        Cache::put($key, $selected, now()->addHours(2));
    }
}
