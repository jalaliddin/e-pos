<div class="">

    @if (session()->has('error'))
        <p class="text-red-500">{{ session('error') }}</p>
    @endif
    <div class="overflow-x-auto md:overflow-x-none">
        <table class="min-w-[600px] min-w-full border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="px-2 py-2 border border-gray-400 text-left w-3/5 dark:text-gray-800">Mahsulot</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-800">Qiymati</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-800">QQS(%)</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-800">Soni</th>
                    <th class="px-2 py-2 border border-gray-400 text-center w-1/6 dark:text-gray-800">Umumiy</th>
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
                    <td colspan="3" class="px-4 py-2 border-r text-right font-semibold">QQS siz</td>
                    <td colspan="2" class="px-4 py-2 text-center font-semibold">{{ $currency_symbol }}{{ number_format($total_price, 2) }}</td>
                </tr>

                @foreach ($total_tax as $rate => $amount)
                    <tr class="border-gray-400 border">
                        <td colspan="3" class="px-4 py-2 border-r text-right font-semibold">QQS @ {{ $rate }}%</td>
                        <td colspan="2" class="px-4 py-2 text-center font-semibold">{{ $currency_symbol }}{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach

                <tr class="bg-gray border-gray-400 border">
                    <td colspan="3" class="px-4 py-2 border-r text-right font-bold">Umumiy</td>
                    <td colspan="2" class="px-4 py-2 text-center font-bold">{{ $currency_symbol }}{{ number_format($grand_total, 2) }}</td>
                </tr>
 
            @endif
            </tbody>
        </table>
    </div>
    <button wire:click="checkout" wire:loading.attr="disabled"  class="w-full bg-green-500 hover:bg-green-600 text-white text-lg font-semibold py-3 rounded-lg shadow-md mt-3 transition duration-200">
        <span wire:loading.remove wire:target='checkout'>Sotish</span>
        <span wire:loading wire:target='checkout' class="w-4 h-4 border-2 border-t-red-100 border-transparent rounded-full animate-spin"></span>
    </button>
    <br>
    <button wire:click="checkoutandprint" wire:loading.attr="disabled"  class="w-full bg-green-500 hover:bg-green-600 text-white text-lg font-semibold py-3 rounded-lg shadow-md mt-3 transition duration-200">
        <span wire:loading.remove wire:target='checkoutandprint'>Chek bilan sotish</span>
        <span wire:loading wire:target='checkoutandprint' class="w-4 h-4 border-2 border-t-red-100 border-transparent rounded-full animate-spin"></span>
    </button>
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
