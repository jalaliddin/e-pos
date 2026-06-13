<div class="">
<style>
    html.dark .pos-cart-row { background-color: #1f2937 !important; border-bottom-color: #374151 !important; }
    html.dark .pos-cart-row td { color: #f1f5f9 !important; border-color: #374151 !important; }
    html.dark .pos-cart-row input { color: #f1f5f9 !important; background-color: #374151 !important; border-color: #4b5563 !important; }
</style>

    @if (session()->has('error'))
        <p class="text-red-500">{{ session('error') }}</p>
    @endif
    <div class="overflow-x-auto md:overflow-x-none">
        <table class="min-w-[600px] min-w-full border border-gray-300 dark:border-gray-600">
            <thead>
                <tr class="bg-gray-200 dark:bg-gray-700">
                    <th class="px-2 py-2 border border-gray-400 text-left w-3/5 dark:text-gray-100">Mahsulot</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-100">Qiymati</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-100">QQS(%)</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-100">Soni</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-100">Umumiy</th>
                </tr>
            </thead>
            <tbody>
            @if ( !is_countable($cartItems) || count($cartItems) < 1)
                <tr class="min-h-32"><td class="p-4">Bo'sh</td></tr>
            @else
                @php 
                    $total_price = 0;
                    $total_tax = [];
                    $grand_total = 0;
                @endphp

                @foreach($cartItems as $item)
                    @php 
                        $tax = $item->tax;
                        $item_total = $item->price * $item->quantity;
                        $tax_amount = ($item_total * $tax) / 100;
                        $item_total_with_tax = $item_total + $tax_amount;
                        $total_price += $item_total;
                        $total_tax[$tax] = ($total_tax[$tax] ?? 0) + $tax_amount;
                        $grand_total += $item_total_with_tax;
                    @endphp
                    <livewire:cart-item :cartItem="$item" :currency_symbol="$currency_symbol" :key="$item->id" />
                @endforeach
                
                <tr class="border-gray-400 border">
                    <td colspan="3" class="px-4 py-2 border-r text-right font-semibold dark:text-gray-200">QQS siz</td>
                    <td colspan="2" class="px-4 py-2 text-center font-semibold dark:text-gray-200">{{ $currency_symbol }}{{ number_format($total_price, 2) }}</td>
                </tr>

                @foreach ($total_tax as $rate => $amount)
                    <tr class="border-gray-400 border">
                        <td colspan="3" class="px-4 py-2 border-r text-right font-semibold dark:text-gray-200">QQS @ {{ $rate }}%</td>
                        <td colspan="2" class="px-4 py-2 text-center font-semibold dark:text-gray-200">{{ $currency_symbol }}{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach

                <tr class="bg-gray border-gray-400 border">
                    <td colspan="3" class="px-4 py-2 border-r text-right font-bold dark:text-white">Umumiy</td>
                    <td colspan="2" class="px-4 py-2 text-center font-bold dark:text-white">{{ $currency_symbol }}{{ number_format($grand_total, 2) }}</td>
                </tr>
 
            @endif
            </tbody>
        </table>
    </div>
    <div class="grid grid-cols-2 gap-4 mt-3">
<button wire:click="checkoutandprint"
        wire:loading.attr="disabled"
        class="w-full bg-red-500 hover:bg-green-600 text-white text-xl font-semibold py-12 rounded-lg shadow-lg transition duration-200">
        
        <span wire:loading.remove wire:target="checkoutandprint">Chek bilan sotish</span>
        <span wire:loading wire:target="checkoutandprint"
            class="mx-auto w-6 h-6 border-4 border-t-white border-transparent rounded-full animate-spin"></span>
    </button>
    
    <button wire:click="checkout"
        wire:loading.attr="disabled"
        class="w-full bg-green-500 hover:bg-green-600 text-white text-xl font-semibold py-12 rounded-lg shadow-lg transition duration-200">
        
        <span wire:loading.remove wire:target="checkout">Sotish</span>
        <span wire:loading wire:target="checkout"
            class="mx-auto w-6 h-6 border-4 border-t-white border-transparent rounded-full animate-spin"></span>
    </button>

    

</div>
</div>
<script>
    document.addEventListener('window-print', e => {
        const printUrl = e.detail.url;
        const printWindow = window.open(printUrl, '_blank');
        printWindow.onload = () => {
            printWindow.print();
        };
    });
</script>
