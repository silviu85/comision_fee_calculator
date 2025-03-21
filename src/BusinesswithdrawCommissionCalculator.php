<?php

namespace App;

class BusinessWithdrawCommissionCalculator implements CommissionCalculatorInterface
{
    private CurrencyConverter $converter;
    private const FEE_PERCENTAGE = 0.005; // 0.5%

    public function __construct(CurrencyConverter $converter)
    {
        $this->converter = $converter;
    }

    public function calculate(Operation $operation): float
    {
        $fee = $operation->amount * self::FEE_PERCENTAGE;
        $decimals = $this->converter->getDecimals($operation->currency);
        return $this->roundUp($fee, $decimals);
    }

    private function roundUp(float $value, int $decimals): float
    {
        $factor = pow(10, $decimals);
        return ceil($value * $factor) / $factor;
    }
}