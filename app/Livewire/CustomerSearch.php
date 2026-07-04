<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;

class CustomerSearch extends Component
{
    public $customers = [];
    public $selectedCustomer = null;
    public $selectedCustomerId = '';

    public function mount(){
        $this->customers = Customer::orderBy('first_name')->get();

        $customerId = session('customer_id');
        if( $customerId ){
            $this->selectedCustomer = Customer::find( $customerId );
            $this->selectedCustomerId = $customerId;
        }
    }

    public function updatedSelectedCustomerId($value)
    {
        if ($value) {
            $this->selectCustomer($value);
        } else {
            $this->clear();
        }
    }

    public function selectCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            session(['customer_id' => $customer->id]);
            $this->selectedCustomer = $customer;
            $this->selectedCustomerId = $customer->id;
            $this->dispatch('customerSelected', $customerId);
        }
    }


    public function clear(){
        session(['customer_id' => null]);
        $this->selectedCustomer = null;
        $this->selectedCustomerId = '';
        $this->dispatch('customerSelected', null);
    }

    public function render()
    {
        return view('livewire.customer-search');
    }
}
