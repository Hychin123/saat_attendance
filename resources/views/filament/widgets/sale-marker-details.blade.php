<div class="p-6">
    <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Sale ID</span>
                <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $sale->sale_id }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer Name</span>
                <span class="text-base text-gray-900 dark:text-white">{{ $sale->customer_name ?? $sale->customer?->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</span>
                <span class="text-base text-gray-900 dark:text-white">{{ $sale->customer_phone ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</span>
                <span class="text-base text-gray-900 dark:text-white">{{ $sale->customer_location ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Agent</span>
                <span class="text-base text-gray-900 dark:text-white">{{ $sale->agent?->name ?? 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Amount</span>
                <span class="text-base font-semibold text-gray-900 dark:text-white">${{ number_format($sale->net_total, 2) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                <span>
                    @php
                        $statusColor = match($sale->status) {
                            'COMPLETED' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            'PROCESSING', 'READY' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            'PENDING', 'DEPOSITED' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                            default => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                        {{ $sale->status }}
                    </span>
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Date</span>
                <span class="text-base text-gray-900 dark:text-white">{{ $sale->created_at->format('M d, Y h:i A') }}</span>
            </div>
        </div>
    </div>
</div>
