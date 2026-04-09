<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Observers\SaleObserver;

class CreateMachinesFromSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machines:create-from-sales {--sale_id= : Specific sale ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create machine records from completed sales containing water vending machines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $saleId = $this->option('sale_id');
        
        if ($saleId) {
            // Process specific sale
            $sale = Sale::with('items.item.category')->where('sale_id', $saleId)->first();
            
            if (!$sale) {
                $this->error("Sale {$saleId} not found");
                return 1;
            }
            
            if ($sale->status !== Sale::STATUS_COMPLETED) {
                $this->error("Sale {$saleId} is not completed (status: {$sale->status})");
                return 1;
            }
            
            $this->processSale($sale);
        } else {
            // Process all completed sales without machines
            $sales = Sale::with('items.item.category')
                ->where('status', Sale::STATUS_COMPLETED)
                ->doesntHave('machines')
                ->get();
            
            if ($sales->isEmpty()) {
                $this->info('No completed sales without machines found');
                return 0;
            }
            
            $this->info("Found {$sales->count()} completed sales without machines");
            
            $bar = $this->output->createProgressBar($sales->count());
            $bar->start();
            
            foreach ($sales as $sale) {
                $this->processSale($sale);
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
        }
        
        $this->info('Done!');
        return 0;
    }
    
    private function processSale(Sale $sale): void
    {
        $observer = app(SaleObserver::class);
        $reflection = new \ReflectionClass($observer);
        $method = $reflection->getMethod('createMachinesForSale');
        $method->setAccessible(true);
        
        $beforeCount = $sale->machines()->count();
        $method->invoke($observer, $sale);
        $afterCount = $sale->machines()->count();
        
        $created = $afterCount - $beforeCount;
        
        if ($created > 0) {
            $this->line("\n✅ Sale {$sale->sale_id}: Created {$created} machine(s)");
        } else {
            $this->line("\n⏭️  Sale {$sale->sale_id}: No water vending machines found");
        }
    }
}
