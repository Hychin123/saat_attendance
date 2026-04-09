<div class="flex flex-col gap-2">
    @foreach($getRecord()->items->take(3) as $stockInItem)
        <div class="flex items-center gap-3">
            @if($stockInItem->item && $stockInItem->item->image)
                <img src="{{ Storage::url($stockInItem->item->image) }}" 
                     alt="{{ $stockInItem->item->item_name }}"
                     class="h-10 w-10 rounded-full object-cover ring-2 ring-white dark:ring-gray-900">
            @else
                <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center ring-2 ring-white dark:ring-gray-900">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
            <div class="flex-1">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $stockInItem->item->item_name ?? 'N/A' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Qty: {{ $stockInItem->quantity }}
                </div>
            </div>
        </div>
    @endforeach
    
    @if($getRecord()->items->count() > 3)
        <div class="text-xs text-gray-500 dark:text-gray-400 italic">
            + {{ $getRecord()->items->count() - 3 }} more items
        </div>
    @endif
</div>
