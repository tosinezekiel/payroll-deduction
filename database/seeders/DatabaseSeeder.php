<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\PayItem;
use App\Models\Business;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Business::factory(10)->create()->each(function ($business) {
            $users = User::factory(5)->create();
            
            foreach ($users as $user) {
                $business->users()->attach($user, ['external_id' => fake()->uuid()]);

                PayItem::factory(rand(1, 3))->create([
                    'user_id' => $user->id,
                    'business_id' => $business->id
                ]);
            }
        });
    }
}
