<?php

namespace App;

class PrivateWithdrawCommissionCalculator implements CommissionCalculatorInterface
{
    private CurrencyConverter $converter;

    /**
     * Array to track each user's weekly usage of the free (0 commission) threshold.
     * Structure: [ userId => [ weekKey => [ 'count' => int, 'freeUsed' => float ] ] ]
     * The free amount is stored in EUR.
     */
    private array $userWeeklyUsage = [];

    private const FEE_PERCENTAGE = 0.003; // 0.3%
    private const FREE_LIMIT_EUR = 1000.00;
    private const MAX_FREE_WITHDRAWALS = 3;

    public function __construct(CurrencyConverter $converter)
    {
        $this->converter = $converter;
    }

    public function calculate(Operation $operation): float
    {
        $weekKey = $this->getWeekKey($operation->date);
        $userId  = $operation->userId;
    
        if (!isset($this->userWeeklyUsage[$userId])) {
            $this->userWeeklyUsage[$userId] = [];
        }
        if (!isset($this->userWeeklyUsage[$userId][$weekKey])) {
            $this->userWeeklyUsage[$userId][$weekKey] = ['count' => 0, 'freeUsed' => 0.0];
        }
    
        // Increment the weekly operation count.
        $this->userWeeklyUsage[$userId][$weekKey]['count']++;
    
        
        // For operations beyond the first 3 in the week, charge on the full amount.
        if ($this->userWeeklyUsage[$userId][$weekKey]['count'] > self::MAX_FREE_WITHDRAWALS) {
            $fee = $operation->amount * self::FEE_PERCENTAGE;
            return $this->roundUp($fee, $this->converter->getDecimals($operation->currency));
        }
    
        $freeUsed = $this->userWeeklyUsage[$userId][$weekKey]['freeUsed'];
        $freeRemainingEur = max(0, self::FREE_LIMIT_EUR - $freeUsed);
    
        // Convert the operation amount to EUR using full precision.
        $amountInEur = $this->converter->convertToEuro($operation->amount, $operation->currency);
        if ($amountInEur <= $freeRemainingEur) {
            // All of the amount is free.
            $this->userWeeklyUsage[$userId][$weekKey]['freeUsed'] += $amountInEur;
            return 0.0;
        }
    
        // Part of the withdrawal exceeds the free limit.
        $taxableEur = ($freeUsed + $amountInEur) - self::FREE_LIMIT_EUR;

        // The free limit is now fully used.
        $this->userWeeklyUsage[$userId][$weekKey]['freeUsed'] = self::FREE_LIMIT_EUR;
    
        // Calculate commission in EUR for the taxable portion.
        $feeInEur = $taxableEur * self::FEE_PERCENTAGE;
        // Convert the commission fee from EUR back to the operation's currency.
        $fee = $this->converter->convertFromEuro($feeInEur, $operation->currency);
        $decimals = $this->converter->getDecimals($operation->currency);
    
        // Round up to the smallest currency unit.
        return $this->roundUp($fee, $decimals);
    }
     
    /**
     * Returns a week identifier in the format 'year-week'
     * so that weeks run from Monday to Sunday.
     */
    private function getWeekKey(\DateTime $date): string
    {
        return $date->format("o-W");
    }

    /**
     * Rounds up a value to the given number of decimal places.
     */
    private function roundUp(float $value, int $decimals): float
    {
        $factor = pow(10, $decimals);
        return ceil($value * $factor) / $factor;
    }
}
