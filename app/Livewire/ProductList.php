<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;

class ProductList extends Component
{
    public $query = '';
    public $selectedCategory = null;
    public $currency_symbol = '$';

    public function render()
    {
        $categories = Category::withCount('products')->get();
        
        $products = Product::query()
            ->when($this->query, function($q) {
                $q->where('name', 'like', '%' . $this->query . '%');
            })
            ->when($this->selectedCategory, function($q) {
                $q->where('category_id', $this->selectedCategory);
            })
            ->get();

        return view('livewire.product-search', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function addToCart($productId)
    {
        // Sizning cart logikangiz
    }
}