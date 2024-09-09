<?php

namespace App\Services;

use App\Models\User;
use App\Models\PayItem;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayItemService
{
    public function processPayItems(array $payItems, Business $business)
    {
        DB::beginTransaction();
        try {
            foreach ($payItems as $item) {
                $businessUser = BusinessUser::where('external_id', $item['employeeId'])->first();

                if (!$businessUser) {
                    Log::warning("No business user found with external ID: {$item['employeeId']}");
                    continue;
                }

                $user = $businessUser->user;

                $deductionPercentage = $business->deduction_percentage ?? 30;
                $amount = round(($item['hoursWorked'] * $item['payRate'] * ($deductionPercentage / 100)) * 100, 0, PHP_ROUND_HALF_UP);
                Log::debug("Processing payment: ", ['amount' => $amount, 'deductionPercentage' => $deductionPercentage]);

                PayItem::updateOrCreate([
                    'external_id' => $item['id'],
                    'user_id' => $user->id,
                    'business_id' => $business->id,
                ], [
                    'hours_worked' => $item['hoursWorked'],
                    'pay_rate' => $item['payRate'],
                    'date' => $item['date'],
                    'amount' => $amount,
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing pay items for business ' . $business->name . ': ' . $e->getMessage());
        }
    }

    public function removeStaleEntries(array $payItems, Business $business)
    {
        $existingIds = collect($payItems)->pluck('id');
        PayItem::where('business_id', $business->id)
               ->whereNotIn('external_id', $existingIds)
               ->delete();
    }
}
