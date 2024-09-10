<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PayItemService;
use PHPUnit\Framework\Attributes\DataProvider;

class PayItemServiceTest extends TestCase
{
    private $payItemService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payItemService = new PayItemService();
    }

    public static function amountProvider(): array
    {
        return [
            'standard case' => [10, 15.5, 10, 15.50],
            'zero hours' =>  [0, 15.5, 10, 0],
            'specific deduction percentage' => [8.5, 12.5, 50, 53.13],
            'default deduction percentage' => [8.5, 12.5, 30, 31.88],
        ];
    }
    
    #[DataProvider('amountProvider')]
    public function testCalculateAmount($hoursWorked, $payRate, $deductionPercentage, $expectedAmount): void
    {
        $result = $this->payItemService->calculateAmount($hoursWorked, $payRate, $deductionPercentage);
        $this->assertEquals($expectedAmount, to_usd($result));
    }
}
