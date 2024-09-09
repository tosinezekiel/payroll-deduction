<?php

namespace Tests\Feature\Jobs;

use Tests\TestCase;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Response;
use App\Services\PayItemService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SyncPayItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_request_made_to_imaginary_api_is_successfull()
    {
        $path = base_path('tests/fixtures/pay_items_response.json');
        $fakeResponse = json_decode(file_get_contents($path), true);
        $clairApiKey = config('services.clair.api_key');

        $business = Business::factory()->create();
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['external_id' => 'theExternalIdOfAnotherUserRelatedToTheSyncTargetBusiness']);

        Http::fake([
            "https://some-partner-website.com/clair-pay-item-sync/{$business->id}?page=1" => Http::response($fakeResponse, Response::HTTP_OK, [
                'x-api-key' => $clairApiKey
            ])
        ]);

        $this->assertEquals(
            $fakeResponse,
            self::payItems()
        );
    }

    public function test_request_made_to_imaginary_api_logs_error_if_client_is_unauthorized()
    {
        Http::fake([
            'https://some-partner-website.com/*' => Http::response(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED)
        ]);

        Log::shouldReceive('alert')->with('Unauthorized access to external API');

        $business = Business::factory()->create();
    
        $response = Http::get("https://some-partner-website.com/clair-pay-item-sync/{$business->id}?page=1");
    
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->status());
    }

    public function test_request_made_to_imaginary_api_logs_critical_if_not_found()
    {
        Http::fake([
            'https://some-partner-website.com/*' => Http::response(['error' => 'Unauthorized'], Response::HTTP_NOT_FOUND)
        ]);

        Log::shouldReceive('critical')->with('No business found for provided external ID');

        $business = Business::factory()->create();
    
        $response = Http::get("https://some-partner-website.com/clair-pay-item-sync/{$business->id}?page=1");
    
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->status());
    }


    public function test_payitems_for_specific_business_is_processed_successfully()
    {
        $path = base_path('tests/fixtures/pay_items_response.json');
        $fakeResponse = json_decode(file_get_contents($path), true);

        $mockData = self::payItems();

        $payItemSampleEmployeeID = $mockData['payItems'][rand(0,1)];

        $business = Business::factory()->create();
        $user = User::factory()->create();
        $business->users()->attach($user->id, ['external_id' => $payItemSampleEmployeeID['employeeId']]);

        $service = new PayItemService();
        
        $service->processItems($fakeResponse['payItems'], $business);

        $this->assertDatabaseHas('pay_items', [
            'external_id' => $payItemSampleEmployeeID['id'],
            'user_id' => $user->id,
            'business_id' => $business->id
        ]);
        
    }

    private static function payItems(): array
    {
        return [
            "payItems" => [
                [
                    "id" => "anExternalIdForThisPayItem",
                    "employeeId" => "theExternalIdOfTheUserRelatedToTheSyncTargetBusiness",
                    "hoursWorked" => 8.5,
                    "payRate" => 12.5,
                    "date" => "2021-10-19"
                ],
                [
                    "id" => "aDifferentExternalIdForThisPayItem",
                    "employeeId" => "theExternalIdOfAnotherUserRelatedToTheSyncTargetBusiness",
                    "hoursWorked" => 10,
                    "payRate" => 8,
                    "date" => "2021-10-18"
                ]
            ],
            "isLastPage" => false
        ];
    }
}
