<div style="padding: 10px; max-width: 300px; font-family: system-ui, -apple-system, sans-serif;">
    <h3 style="margin: 0 0 10px 0; font-weight: bold; color: #1f2937; font-size: 16px;">
        Sale ID: {{ $sale->sale_id }}
    </h3>
    <div style="font-size: 14px; line-height: 1.8;">
        <p style="margin: 5px 0;">
            <strong>Customer:</strong> {{ $sale->customer_name ?? $sale->customer?->name ?? 'N/A' }}
        </p>
        <p style="margin: 5px 0;">
            <strong>Phone:</strong> {{ $sale->customer_phone ?? 'N/A' }}
        </p>
        <p style="margin: 5px 0;">
            <strong>Location:</strong> {{ $sale->customer_location ?? 'N/A' }}
        </p>
        <p style="margin: 5px 0;">
            <strong>Agent:</strong> {{ $sale->agent?->name ?? 'N/A' }}
        </p>
        <p style="margin: 5px 0;">
            <strong>Amount:</strong> ${{ number_format($sale->net_total, 2) }}
        </p>
        <p style="margin: 5px 0;">
            <strong>Status:</strong> 
            <span style="padding: 2px 8px; border-radius: 4px; background: #e5e7eb; font-weight: 600; font-size: 12px;">
                {{ $sale->status }}
            </span>
        </p>
        <p style="margin: 5px 0;">
            <strong>Date:</strong> {{ $sale->created_at->format('M d, Y') }}
        </p>
    </div>
</div>
