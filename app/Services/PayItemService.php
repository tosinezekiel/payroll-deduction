<?php

namespace App\Services;

use App\Models\User;
use App\Models\PayItem;
use App\DTOs\PayItemDTO;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayItemService
{
    public function processItems(array $payItems, Business $business)
    {
        DB::beginTransaction();
        try {
            foreach ($payItems as $item) {
                $item = new PayItemDTO($item);

                $businessUser = BusinessUser::where('external_id', $item->employeeId)->first();

                if (!$businessUser) {
                    Log::warning("No business user found with external ID: " . $item->employeeId);
                    continue;
                }

                $user = $businessUser->user;

                $deductionPercentage = $business->deduction_percentage;
                $amount = $this->calculateAmount($item->hoursWorked, $item->payRate, $deductionPercentage);

                $this->createOrUpdatePayItem($item, $user, $business, $amount);
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

    public function calculateAmount(float $hoursWorked, float $payRate, float $deductionPercentage): int
    {
        $deductionMultiplier = $deductionPercentage / 100;
        $amount = $hoursWorked * $payRate * $deductionMultiplier;
        $amountInCents = round($amount * 100, 0, PHP_ROUND_HALF_UP);
        
        return (int) $amountInCents;
    }

    private function createOrUpdatePayItem(PayItemDTO $item, User $user, Business $business, int $amount): void
    {
        $pi = PayItem::updateOrCreate([
            'external_id' => $item->externalId,
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], [
            'hours_worked' => $item->hoursWorked,
            'pay_rate' => $item->payRate,
            'date' => $item->date,
            'amount' => $amount,
        ]);

        dd($pi);
    }
}
