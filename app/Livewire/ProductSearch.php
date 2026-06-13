<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use Livewire\Attributes\On;

class ProductSearch extends Component
{
    use WithPagination;

    public $query = '';
    public $selectedCategory = null;

    public function updatedQuery()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $this->selectedCategory == $categoryId ? null : $categoryId;
        $this->resetPage();
    }

    public function render()
    {
        $currency_symbol = config('settings.currency_symbol');

        $categories = Category::withCount('products')->get();

        $products = Product::query()
            ->when($this->query, fn($q) => $q->where('name', 'like', '%' . $this->query . '%'))
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->paginate(12, ['*'], 'product_page');

        return view('livewire.product-search', compact('currency_symbol', 'categories', 'products'));
    }

    #[On('checkout-completed')]
    public function checkoutCompleted()
    {
        $this->query = '';
        $this->resetPage();
    }

    #[On('cartUpdated')]
    public function updateCart()
    {
    }

    public function addToCart($product_id, $quantity = 1)
    {
        $product  = Product::find($product_id);
        $user_id  = auth()->user()->id;
        $cartItem = Cart::firstOrCreate(
            ['user_id' => $user_id, 'product_id' => $product_id],
            [
                'quantity' => 0,
                'name' => $product->name,
                'price' => $product->price,
                'tax' => $product->tax
            ]
        );

        if ($product->quantity < ($cartItem->quantity + $quantity)) {
            if ($cartItem->quantity < 1) {
                $cartItem->delete();
            }
            return;
        }

        $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);

        $this->dispatch('cartUpdated');
    }
}
