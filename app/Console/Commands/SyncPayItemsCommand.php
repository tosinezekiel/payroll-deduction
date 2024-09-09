<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Jobs\SyncPayItems;
use Illuminate\Console\Command;

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $businessId = $this->argument('businessId');
        $business = Business::find($businessId);

        if (!$business) {
            $this->fail("Business with ID $businessId not found.");
        }

        
        SyncPayItems::dispatch($business); 

        $this->info("Job dispatched successfully for business ID: $businessId");

        return 0;
    }
}
