<?php

namespace App\DTOs;

class PayItemDTO
{
    public float $hoursWorked;
    public float $payRate;
    public string $employeeId;
    public string $externalId;
    public string $date;

    public function __construct(array $data)
    {
        $this->hoursWorked = $data['hoursWorked'];
        $this->payRate = $data['payRate'];
        $this->employeeId = $data['employeeId'];
        $this->externalId = $data['id'];
        $this->date = $data['date'];
    }
}
