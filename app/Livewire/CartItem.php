<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\On; 

class CartItem extends Component
{
    public $cartItem;

    public $currency_symbol;

    public $quantity;
    public $price;

    public function mount($cartItem)
    {
        $this->cartItem = $cartItem;
        $this->quantity = $cartItem->quantity;
        $this->price = $cartItem->price;
    }

    #[On('cartUpdated')]
    public function cartUpdated(){
        $this->quantity = $this->cartItem->quantity;
        $this->price = $this->cartItem->price;
    }

    public function removeFromCart()
    {
        $this->quantity = 0;
        $this->cartItem->delete();
        $this->dispatch('cartUpdatedFromItem');
    }

    public function updated(){
        if ($this->quantity > 0 && $this->price >=0) {  
            $product = Product::find( $this->cartItem->product_id );
            if( $product->quantity <  $this->quantity ){  
                $this->quantity = $product->quantity;
            }
            $this->cartItem->quantity = $this->quantity;
            $this->cartItem->price = $this->price;
            $this->cartItem->save();
            $this->dispatch('cartUpdatedFromItem');
        } 
    }

    public function render()
    {  
        return view('livewire.cart-item');
    }
}
