
<div class="mx-auto">
    {{-- Search --}}
    <div class="relative mb-3">
        <input wire:model.live.debounce.250ms="query" type="search"
            class="bg-white block w-full text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
            placeholder="Search product..." />
    </div>

    {{-- Category filters --}}
    <div class="flex flex-wrap gap-1 mb-3">
        <button wire:click="selectCategory(null)"
            class="px-2 py-1 text-xs rounded-full border transition-colors
                {{ $selectedCategory === null
                    ? 'bg-primary-600 text-white border-primary-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:text-gray-800 dark:border-gray-400' }}">
            Hammasi
        </button>
        @foreach ($categories as $category)
        <button wire:click="selectCategory({{ $category->id }})"
            class="px-2 py-1 text-xs rounded-full border transition-colors
                {{ $selectedCategory == $category->id
                    ? 'bg-primary-600 text-white border-primary-600'
                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:text-gray-800 dark:border-gray-400' }}">
            {{ $category->name }}
            <span class="opacity-60">({{ $category->products_count }})</span>
        </button>
        @endforeach
    </div>

    {{-- Products grid --}}
    <div class="grid grid-cols-2 md:grid-cols-1 lg:grid-cols-3 gap-2">
        @forelse ($products as $product)
        <div wire:click="addToCart({{ $product->id }})"
            class="relative bg-white border border-gray-300 rounded overflow-hidden cursor-pointer hover:border-primary-400 transition-colors">

            <div wire:loading wire:target="addToCart({{ $product->id }})"
                class="bg-gray-200 bg-opacity-80 absolute p-2 w-full h-full text-red-500">
                <svg class="absolute h-12 w-12 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
                    viewBox="0 0 120 30" fill="currentColor">
                    <circle cx="15" cy="15" r="10" fill="red">
                        <animate attributeName="opacity" values="0;1;0" dur="1.5s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="60" cy="15" r="10" fill="red">
                        <animate attributeName="opacity" values="0;1;0" dur="1.5s" repeatCount="indefinite" begin="0.3s"/>
                    </circle>
                    <circle cx="105" cy="15" r="10" fill="red">
                        <animate attributeName="opacity" values="0;1;0" dur="1.5s" repeatCount="indefinite" begin="0.6s"/>
                    </circle>
                </svg>
            </div>

            <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}" class="object-contain max-h-full">
            <p class="text-gray-700 p-2 text-sm">{{ $product->name }} ({{ $product->quantity }})</p>
            <p class="text-gray-700 p-2 pt-0 text-md font-medium">{{ $currency_symbol . $product->price }}</p>

            @if ($product->quantity < 1)
            <div class="absolute top-1 right-1 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">
                Out of Stock
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-3 text-center text-gray-400 dark:text-gray-400 py-8 text-sm">Mahsulot topilmadi</div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($products->hasPages())
    <div class="mt-3 flex items-center justify-between gap-2">
        <button
            wire:click="previousPage('product_page')"
            @if($products->onFirstPage()) disabled @endif
            class="flex-1 py-1.5 text-sm rounded-lg border transition-colors
                {{ $products->onFirstPage() ? 'border-gray-200 text-gray-300 dark:border-gray-700 dark:text-gray-500 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700' }}">
            ← Oldingi
        </button>
        <span class="text-xs text-gray-400 dark:text-gray-300 shrink-0">
            {{ $products->currentPage() }} / {{ $products->lastPage() }}
        </span>
        <button
            wire:click="nextPage('product_page')"
            @if(!$products->hasMorePages()) disabled @endif
            class="flex-1 py-1.5 text-sm rounded-lg border transition-colors
                {{ !$products->hasMorePages() ? 'border-gray-200 text-gray-300 dark:border-gray-700 dark:text-gray-500 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700' }}">
            Keyingi →
        </button>
    </div>
    @endif
</div>
