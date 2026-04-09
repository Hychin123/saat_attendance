<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Sale;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        // Update sale after payment
        $payment = $this->record;
        $sale = Sale::where('sale_id', $payment->sale_id)->first();

        if ($sale) {
            // Update deposit and remaining amounts
            $totalPaid = $sale->payments()->sum('amount');
            $sale->deposit_amount = $totalPaid;
            $sale->remaining_amount = $sale->net_total - $totalPaid;

            // Update status based on payment
            if ($sale->status === Sale::STATUS_PENDING && $totalPaid > 0) {
                $sale->status = Sale::STATUS_DEPOSITED;
            }

            if ($sale->remaining_amount <= 0 && $sale->status === Sale::STATUS_READY) {
                $sale->status = Sale::STATUS_COMPLETED;
                $sale->completed_date = now();
            }

            $sale->save();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
