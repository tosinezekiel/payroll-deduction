<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use App\Services\PayItemService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PayItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_item_calculation_with_deduction_percentage()
    {
        $business = Business::factory()->create(['deduction_percentage' => 50]);
        $user = User::factory()->create();
        $business->users()->attach($user, ['external_id' => 'exampleExternalId']);
        $payItems = [
            ['id' => 'item1', 'employeeId' => 'exampleExternalId', 'hoursWorked' => 8.5, 'payRate' => 12.5, 'date' => '2021-10-19']
        ];

        (new PayItemService())->processPayItems($payItems, $business);

        $this->assertDatabaseHas('pay_items', [
            'external_id' => 'item1',
            'amount' => 5313 
        ]);
    }

    public function test_pay_item_calculation_without_deduction_percentage()
    {
        $business = Business::factory()->create(['deduction_percentage' => null]);
        $user = User::factory()->create();
        $business->users()->attach($user, ['external_id' => 'exampleExternalId']);
        $payItems = [
            ['id' => 'item1', 'employeeId' => 'exampleExternalId', 'hoursWorked' => 8.5, 'payRate' => 12.5, 'date' => '2021-10-19']
        ];

        (new PayItemService())->processPayItems($payItems, $business);

        $this->assertDatabaseHas('pay_items', [
            'external_id' => 'item1',
            'amount' => 3188 
        ]);
    }
}
