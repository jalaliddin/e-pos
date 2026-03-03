<?php

namespace App\Livewire;

use App\Models\Cart as CartModel;
use App\Models\Order;
use App\Models\Product;
use Livewire\Attributes\On;
use Livewire\Component;

class Cart extends Component
{
    public $cartItems = [];

    private $currency_symbol;

    public function mount()
    {
        $this->cartItems = CartModel::with('product')
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->get();

        $this->currency_symbol = config('settings.currency_symbol');
    }

    public function render()
    {
        return view('livewire.cart', ['cartItems' => $this->cartItems, 'currency_symbol' => $this->currency_symbol]);
    }

    #[On('cartUpdated')]
    public function updateCart()
    {
        $this->cartItems = CartModel::with('product')
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->get();

        $this->currency_symbol = config('settings.currency_symbol');

    }

    #[On('cartUpdatedFromItem')]
    public function cartUpdatedFromItem()
    {
        $this->cartItems = CartModel::with('product')
            ->where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->get();

        $this->currency_symbol = config('settings.currency_symbol');

    }

    public function checkout()
    {

        $total_price = 0;
        $income_price = 0;
        $customerId = session('customer_id');

        if (empty($customerId)) {
            return $this->dispatch('error', error: 'Please select customer!');
        }

        $items = $this->cartItems;

        if (! is_countable($items) || count($items) < 1) {
            return;
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'total_price' => $total_price,
            'income_price' => $income_price,
        ]);

        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            // dump($product);

            $order->items()->create([
                'name' => $item->name,
                'income_price' => $product->income_price,
                'price' => $item->price,
                'tax' => $item->tax,
                'quantity' => $item->quantity,
                'product_id' => $item->product_id,
                'category_name' => $product->category->name ?? 'kategoriyasiz',
            ]);
            $total_price += $item->quantity * $item->price;
            $income_price += $item->quantity * $product->income_price;
            $product->quantity = $product->quantity - $item->quantity;
            $product->save();
        }

        $order->total_price = $total_price;
        $order->income_price = $income_price;
        $order->save();

        $this->cartItems = CartModel::where('user_id', auth()->user()->id)
            ->delete();

        $this->dispatch('checkout-completed');

        // redirect( url('/admin/orders/'. $order->id .'/edit') );
        redirect(url('/admin/pos'));
        // redirect( url('/admin/pos') );

    }

    public function botCheckout($botProductIds, $botCustomerId, $amount)  
    {

        $total_price = 0;
        $income_price = 0;
        // $customerId = session('customer_id');

        // if (empty($botCustomerId)) {
        //     return $this->dispatch('error', error: 'Please select customer!');
        // }

        // $items = $this->cartItems;

        // if (! is_countable($items) || count($items) < 1) {
        //     return;
        // }

        $order = Order::create([
            'customer_id' => $botCustomerId,
            'total_price' => $total_price
        ]);

        if (! is_array($botProductIds)) {
            $botProductIds = [$botProductIds];
        }

        $productCounts = [];

        foreach ($botProductIds as $productId) {
            $productCounts[$productId] = ($productCounts[$productId] ?? 0) + 1;
        }

        foreach ($productCounts as $productId => $quantity) {
            $product = Product::find($productId);

            if (! $product) {
                continue;
            }

            $order->items()->create([
                'name' => $product->name,
                'income_price' => $product->income_price,
                'price' => $product->price,
                'tax' => 0,
                'quantity' => $quantity,
                'product_id' => $productId,
                'category_name' => $product->category->name ?? 'kategoriyasiz',
            ]);

            $income_price += $quantity * $product->income_price;
            $product->quantity = max(0, $product->quantity - $quantity);
            $product->save();
        }
        

        $order->total_price = $amount;
        $order->income_price = $income_price;
        $order->save();

        // $this->cartItems = CartModel::where('user_id', auth()->user()->id)
        //     ->delete();

        $this->dispatch('checkout-completed');

        return $order;
        // redirect( url('/admin/orders/'. $order->id .'/edit') );
        // redirect(url('/admin/pos'));
        // redirect( url('/admin/pos') );

    }

    public function checkoutandprint()
    {

        $total_price = 0;
        $income_price = 0;
        $customerId = session('customer_id');

        if (empty($customerId)) {
            return $this->dispatch('error', error: 'Please select customer!');
        }

        $items = $this->cartItems;

        if (! is_countable($items) || count($items) < 1) {
            return;
        }

        $order = Order::create([
            'customer_id' => $customerId,
            'total_price' => $total_price,
            'income_price' => $income_price,
        ]);

        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            // dump($product);

            $order->items()->create([
                'name' => $item->name,
                'income_price' => $product->income_price,
                'price' => $item->price,
                'tax' => $item->tax,
                'quantity' => $item->quantity,
                'product_id' => $item->product_id,
                'category_name' => $product->category->name ?? 'kategoriyasiz',
            ]);
            $total_price += $item->quantity * $item->price;
            $income_price += $item->quantity * $product->income_price;
            $product->quantity = $product->quantity - $item->quantity;
            $product->save();
        }

        $order->total_price = $total_price;
        $order->income_price = $income_price;
        $order->save();

        $this->cartItems = CartModel::where('user_id', auth()->user()->id)
            ->delete();

        $this->dispatch('checkout-completed');
        $this->dispatch('window-print', url: url('print/' . $order->id));

        // redirect( url('/admin/orders/'. $order->id .'/edit') );
        redirect(url('/admin/pos'));

    }
}
