<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Jobs\SyncPayItems;
use Illuminate\Console\Command;
use App\Services\PayItemService;

class SyncPayItemsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-pay-items {businessId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes pay items for a specific business';

    public function handle(): int
    {
        $businessId = $this->argument('businessId');
        $business = Business::find($businessId);

        if (!$business) {
            $this->error("Business with ID $businessId not found.");
            return 1; 
        }

        $payItemService = resolve(PayItemService::class);
        SyncPayItems::dispatch($business, $payItemService); 

        $this->info("Job dispatched successfully for business ID: $businessId");

        return 0;
    }
}
