<x-filament-panels::page>
@php
    $sym = $currency ?? '$';
    $fmt = fn($n) => $sym . number_format($n, 0, ',', ' ');
    $pct = fn($n) => number_format($n, 1) . '%';
    $periods = ['today' => 'Bugun', 'week' => 'Bu hafta', 'month' => 'Bu oy', 'year' => 'Bu yil', 'all' => 'Hammasi'];
    $periodLabel = $periods[$period] ?? 'Bu oy';
@endphp

{{-- Period filter --}}
<div class="flex flex-wrap items-center gap-2 mb-6">
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mr-1">Davr:</span>
    @foreach($periods as $key => $label)
    <button wire:click="setPeriod('{{ $key }}')"
        class="px-4 py-1.5 text-sm rounded-full border font-medium transition-all duration-150
            {{ $period === $key
                ? 'bg-primary-600 text-white border-primary-600 shadow-sm'
                : 'bg-white text-gray-600 border-gray-200 hover:border-primary-400 hover:text-primary-600 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600' }}">
        {{ $label }}
    </button>
    @endforeach
</div>

{{-- KPI Summary Cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- Revenue --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start mb-3">
            <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-900/30">
                <x-heroicon-o-banknotes class="w-5 h-5 text-blue-600 dark:text-blue-400"/>
            </div>
            @if(!is_null($summary['revChange']))
            <span class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full
                {{ $summary['revChange'] >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                @if($summary['revChange'] >= 0)
                    <x-heroicon-m-arrow-trending-up class="w-3 h-3"/> +{{ $pct($summary['revChange']) }}
                @else
                    <x-heroicon-m-arrow-trending-down class="w-3 h-3"/> {{ $pct($summary['revChange']) }}
                @endif
            </span>
            @endif
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ $fmt($summary['revenue']) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Jami savdo — {{ $periodLabel }}</p>
    </div>

    {{-- Profit --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start mb-3">
            <div class="p-2.5 rounded-xl bg-green-50 dark:bg-green-900/30">
                <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-green-600 dark:text-green-400"/>
            </div>
            <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                Margin: {{ $pct($summary['margin']) }}
            </span>
        </div>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400 leading-tight">{{ $fmt($summary['profit']) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sof foyda — {{ $periodLabel }}</p>
    </div>

    {{-- Orders --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start mb-3">
            <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-900/30">
                <x-heroicon-o-shopping-bag class="w-5 h-5 text-purple-600 dark:text-purple-400"/>
            </div>
            @if(!is_null($summary['ordChange']))
            <span class="flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full
                {{ $summary['ordChange'] >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400' }}">
                @if($summary['ordChange'] >= 0)
                    <x-heroicon-m-arrow-trending-up class="w-3 h-3"/> +{{ $pct($summary['ordChange']) }}
                @else
                    <x-heroicon-m-arrow-trending-down class="w-3 h-3"/> {{ $pct($summary['ordChange']) }}
                @endif
            </span>
            @endif
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ number_format($summary['orders']) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Buyurtmalar — {{ $periodLabel }}</p>
    </div>

    {{-- Avg order + items --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start mb-3">
            <div class="p-2.5 rounded-xl bg-orange-50 dark:bg-orange-900/30">
                <x-heroicon-o-cube class="w-5 h-5 text-orange-600 dark:text-orange-400"/>
            </div>
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ number_format($summary['items']) }} dona</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ $fmt($summary['avgOrder']) }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">O'rtacha buyurtma — {{ $periodLabel }}</p>
    </div>

</div>

{{-- Sales Chart + Category breakdown --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

    {{-- Daily Sales Bar Chart --}}
    <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Savdo dinamikasi</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kunlik daromad va foyda</p>
            </div>
            <x-heroicon-o-chart-bar class="w-5 h-5 text-gray-300"/>
        </div>
        <div class="p-5">
            @if($dailySales->isNotEmpty())
            @php
                $maxRev  = $dailySales->max('revenue') ?: 1;
                $maxH    = 120;
            @endphp
            <div class="flex items-end gap-0.5 h-32 overflow-x-auto pb-1" style="min-width:0">
                @foreach($dailySales as $day)
                @php
                    $revH   = max(3, ($day->revenue / $maxRev) * $maxH);
                    $profit = max(0, $day->revenue - $day->cost);
                    $proH   = max(3, ($profit / $maxRev) * $maxH);
                @endphp
                <div class="flex flex-col items-center flex-1 min-w-[18px] group relative">
                    {{-- Tooltip --}}
                    <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded-lg px-2.5 py-1.5 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 shadow-lg">
                        <div class="font-semibold">{{ \Carbon\Carbon::parse($day->date)->format('d M') }}</div>
                        <div class="text-blue-300">{{ $sym }}{{ number_format($day->revenue, 0, ',', ' ') }}</div>
                        <div class="text-green-300">+{{ $sym }}{{ number_format($profit, 0, ',', ' ') }}</div>
                    </div>
                    <div class="relative w-full flex flex-col justify-end" style="height: {{ $maxH }}px">
                        <div class="w-full bg-blue-100 dark:bg-blue-900/30 rounded-t transition-all duration-500"
                            style="height: {{ $revH }}px">
                            <div class="w-full bg-green-400 dark:bg-green-500 rounded-t opacity-80"
                                style="height: {{ $proH }}px"></div>
                        </div>
                    </div>
                    <span class="text-[8px] text-gray-300 mt-1 transform whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($day->date)->format('d') }}
                    </span>
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-4 mt-3">
                <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-3 rounded-sm bg-blue-100 dark:bg-blue-900/30 inline-block"></span>Savdo</span>
                <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-3 h-3 rounded-sm bg-green-400 inline-block"></span>Foyda</span>
            </div>
            @else
            <div class="flex flex-col items-center justify-center h-32 text-gray-300 dark:text-gray-600">
                <x-heroicon-o-chart-bar class="w-10 h-10 mb-2"/>
                <p class="text-sm">Ma'lumot yo'q</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Category Breakdown --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Kategoriyalar</h3>
                <p class="text-xs text-gray-400 mt-0.5">Foyda bo'yicha</p>
            </div>
            <x-heroicon-o-squares-2x2 class="w-5 h-5 text-gray-300"/>
        </div>
        <div class="p-5 space-y-4">
            @forelse($categoryStats as $i => $cat)
            @php
                $maxP = $categoryStats->max('total_profit') ?: 1;
                $bar  = max(4, ($cat->total_profit / $maxP) * 100);
                $colors = ['bg-blue-500','bg-purple-500','bg-orange-500','bg-teal-500','bg-pink-500','bg-indigo-500'];
                $color = $colors[$i % count($colors)];
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 truncate max-w-[130px]">
                        {{ $cat->category_name ?: 'Kategoriyasiz' }}
                    </span>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs text-gray-400">{{ number_format($cat->total_qty) }} dona</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $fmt($cat->total_profit) }}</span>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="{{ $color }} h-1.5 rounded-full transition-all duration-700" style="width: {{ $bar }}%"></div>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-8 text-gray-300 dark:text-gray-600">
                <x-heroicon-o-squares-2x2 class="w-8 h-8 mb-2"/>
                <p class="text-sm">Ma'lumot yo'q</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Hourly heatmap + Low stock --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Hourly heatmap --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Soatlik faollik</h3>
                <p class="text-xs text-gray-400 mt-0.5">Qaysi soatlarda savdo ko'p</p>
            </div>
            <x-heroicon-o-clock class="w-5 h-5 text-gray-300"/>
        </div>
        <div class="p-5">
            @php $maxHourly = collect($hourlyStats)->max('orders_count') ?: 1; @endphp
            <div class="grid grid-cols-12 gap-1">
                @for($h = 0; $h < 24; $h++)
                @php
                    $stat   = $hourlyStats->get($h);
                    $cnt    = $stat ? $stat->orders_count : 0;
                    $intens = $maxHourly > 0 ? $cnt / $maxHourly : 0;
                    $opacity = $cnt > 0 ? max(0.15, $intens) : 0.05;
                @endphp
                <div class="group relative">
                    <div class="w-full aspect-square rounded-md bg-primary-500 cursor-default transition-transform hover:scale-110"
                        style="opacity: {{ $opacity }}"></div>
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-gray-900 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                        {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 — {{ $cnt }} ta buyurtma
                    </div>
                </div>
                @endfor
            </div>
            <div class="flex justify-between mt-2 text-[10px] text-gray-400">
                @for($h = 0; $h < 24; $h += 3)
                <span>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}h</span>
                @endfor
            </div>
        </div>
    </div>

    {{-- Low stock warning --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Tugayotgan mahsulotlar</h3>
                <p class="text-xs text-gray-400 mt-0.5">5 ta va undan kam qolganlar</p>
            </div>
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-orange-400"/>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700">
            @forelse($lowStock as $product)
            <div class="flex items-center justify-between px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center gap-3">
                    <img src="{{ $product->getImageUrl() }}" class="w-8 h-8 rounded-lg object-cover bg-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->category->name ?? '—' }}</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                    {{ $product->quantity == 0 ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' }}">
                    {{ $product->quantity == 0 ? 'Tugagan' : $product->quantity . ' ta qoldi' }}
                </span>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-8 text-gray-300 dark:text-gray-600">
                <x-heroicon-o-check-circle class="w-8 h-8 mb-2 text-green-400"/>
                <p class="text-sm text-gray-500">Barcha mahsulotlar yetarli</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Top products tables --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Top selling --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Ko'p sotiladigan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Miqdor bo'yicha top 10</p>
            </div>
            <x-heroicon-o-fire class="w-5 h-5 text-orange-400"/>
        </div>
        <div class="overflow-hidden">
            @forelse($topProducts as $i => $p)
            @php
                $medals = ['🥇','🥈','🥉'];
                $maxQty = $topProducts->max('total_qty') ?: 1;
                $bar = ($p->total_qty / $maxQty) * 100;
            @endphp
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <span class="text-base w-6 text-center shrink-0">
                    @if($i < 3) {{ $medals[$i] }} @else <span class="text-sm text-gray-400">{{ $i+1 }}</span> @endif
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $p->name }}</p>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1 mt-1.5">
                        <div class="bg-orange-400 h-1 rounded-full" style="width: {{ $bar }}%"></div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($p->total_qty) }} dona</p>
                    <p class="text-xs text-gray-400">{{ $fmt($p->total_revenue) }}</p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                <x-heroicon-o-inbox class="w-10 h-10 mb-2"/>
                <p class="text-sm">Ma'lumot yo'q</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Top profit --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">Eng foydali</h3>
                <p class="text-xs text-gray-400 mt-0.5">Foyda bo'yicha top 10</p>
            </div>
            <x-heroicon-o-sparkles class="w-5 h-5 text-yellow-400"/>
        </div>
        <div class="overflow-hidden">
            @forelse($topProfitProducts as $i => $p)
            @php
                $medals = ['🥇','🥈','🥉'];
                $maxP = $topProfitProducts->max('total_profit') ?: 1;
                $bar = ($p->total_profit / $maxP) * 100;
            @endphp
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                <span class="text-base w-6 text-center shrink-0">
                    @if($i < 3) {{ $medals[$i] }} @else <span class="text-sm text-gray-400">{{ $i+1 }}</span> @endif
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $p->name }}</p>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1 mt-1.5">
                        <div class="bg-green-400 h-1 rounded-full" style="width: {{ $bar }}%"></div>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-bold text-green-600 dark:text-green-400">{{ $fmt($p->total_profit) }}</p>
                    <p class="text-xs text-gray-400">{{ number_format($p->total_qty) }} dona</p>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                <x-heroicon-o-inbox class="w-10 h-10 mb-2"/>
                <p class="text-sm">Ma'lumot yo'q</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
</x-filament-panels::page>
