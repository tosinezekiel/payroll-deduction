<?php

namespace Tests\Feature\Console;

use Tests\TestCase;
use App\Models\Business;
use App\Jobs\SyncPayItems;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncPayItemsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_with_invalid_business()
    {
        $this->artisan('app:sync-pay-items', ['businessId' => 999])
             ->expectsOutput('Business with ID 999 not found.')
             ->assertExitCode(1);
    }

    public function test_command_with_valid_business()
    {
        Queue::fake();

        $business = Business::factory()->create();

        $this->artisan('app:sync-pay-items', ['businessId' => $business->id])
             ->expectsOutput("Job dispatched successfully for business ID: {$business->id}")
             ->assertExitCode(0);

        Queue::assertPushed(function (SyncPayItems $job) use ($business) {
            return $job->getBusiness()->id === $business->id;
        });
    }
}
