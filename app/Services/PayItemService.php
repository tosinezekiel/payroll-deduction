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
    public function processItems(array $payItems, Business $business): void
    {
        DB::beginTransaction();
        try {
            foreach ($payItems as $item) {
                $itemDTO = new PayItemDTO($item);

                $businessUser = BusinessUser::where('external_id', $itemDTO->employeeId)->first();

                if (!$businessUser) {
                    Log::warning("No business user found with external ID: " . $itemDTO->employeeId);
                    continue;
                }

                $user = $businessUser->user;

                $deductionPercentage = $business->deduction_percentage;
                $amount = $this->calculateAmount($itemDTO->hoursWorked, $itemDTO->payRate, $deductionPercentage);

                $this->createOrUpdatePayItem($itemDTO, $user, $business, $amount);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing pay items for business ' . $business->name . ': ' . $e->getMessage());
        }
    }

    public function removeStaleEntries(array $payItems, Business $business): void
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

    private function createOrUpdatePayItem(PayItemDTO $itemDTO, User $user, Business $business, int $amount): void
    {
        PayItem::updateOrCreate([
            'external_id' => $itemDTO->externalId,
            'user_id' => $user->id,
            'business_id' => $business->id,
        ], [
            'hours_worked' => $itemDTO->hoursWorked,
            'pay_rate' => $itemDTO->payRate,
            'date' => $itemDTO->date,
            'amount' => $amount,
        ]);
    }
}
