<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Business;
use App\Models\User;
use App\Services\PayItemService;
use Tests\TestCase;

class PayItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_pay_item_calculation_with_deduction_percentage()
    {
        $faker = \Faker\Factory::create();

        $business = Business::factory()->create(['deduction_percentage' => 50]);

        $user = User::factory()->create();
        $externalId = $faker->uuid; 
        $business->users()->attach($user, ['external_id' => $externalId]);

        $business->refresh();

        $payItems = [
            [
                'id' => $faker->uuid, 
                'employeeId' => $externalId,
                'hoursWorked' => 8.5, 
                'payRate' => 12.5, 
                'date' => $faker->date()
            ]
        ];

        // [8.5, 12.5, 50, 53.13]

        (new PayItemService())->processItems($payItems, $business);

        foreach ($payItems as $item) {
            $expectedAmount = (new PayItemService())->calculateAmount($item['hoursWorked'], $item['payRate'], 50);
            $this->assertDatabaseHas('pay_items', [
                'external_id' => $item['id'],
                'user_id' => $user->id,
                'business_id' => $business->id,
                'amount' => $expectedAmount
            ]);
        }
    }


    public function test_pay_item_calculation_without_deduction_percentage()
    {
        $faker = \Faker\Factory::create();

        $business = Business::factory()->create(['deduction_percentage' => 30]);
        $user = User::factory()->create();
        $externalId = $faker->uuid;
        $business->users()->attach($user, ['external_id' => $externalId]);

        $payItems = [
            [
                'id' => $faker->uuid,
                'employeeId' => $externalId,
                'hoursWorked' => $faker->randomFloat(2, 1, 24),
                'payRate' => $faker->randomFloat(2, 10, 100),
                'date' => $faker->date()
            ]
        ];

        (new PayItemService())->processItems($payItems, $business);

        foreach ($payItems as $item) {
            $expectedAmount = (new PayItemService())->calculateAmount($item['hoursWorked'], $item['payRate'], 30);
            $this->assertDatabaseHas('pay_items', [
                'external_id' => $item['id'],
                'amount' => $expectedAmount
            ]);
        }
    }
}
