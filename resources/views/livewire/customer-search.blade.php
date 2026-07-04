<div class="relative w-full">
    <select
        wire:model.live="selectedCustomerId"
        class="bg-white block w-full text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
    >
        <option value="">Select Customer...</option>
        @foreach($customers as $customer)
            <option value="{{ $customer->id }}">
                {{ $customer->first_name . ' ' . $customer->last_name }}
            </option>
        @endforeach
    </select>
</div>
