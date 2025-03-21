<?php

namespace App;

class TestCurrencyConverter extends CurrencyConverter
{
    /**
     * Override parent's constructor.
     *
     * We deliberately do not call parent::__construct() so that no API call is made.
     */
    public function __construct()
    {
        // Intentionally left empty.
    }
    
    /**
     * Returns a fixed conversion rate for the given currency.
     *
     * @param string $currency The currency code (e.g., "USD", "JPY").
     * @return float The fixed conversion rate.
     * @throws \Exception If the currency is not defined.
     */
    public function getRate(string $currency): float
    {
        $fixedRates = [
            'EUR' => 1.0,
            'USD' => 1.1497,
            'JPY' => 129.53,
        ];
        
        if (isset($fixedRates[$currency])) {
            return $fixedRates[$currency];
        }
        
        throw new \Exception("Test rate for currency {$currency} is not defined.");
    }
    
    /**
     * Returns the number of decimal places for the given currency.
     *
     * @param string $currency
     * @return int
     */
    public function getDecimals(string $currency): int
    {
        $decimals = [
            'EUR' => 2,
            'USD' => 2,
            'JPY' => 0,
        ];
        
        return $decimals[strtoupper($currency)] ?? 2;
    }
    
    /**
     * Converts an amount (in the given currency) to EUR using the fixed rate.
     *
     * @param float  $amount
     * @param string $currency
     * @return float
     */
    public function convertToEuro(float $amount, string $currency): float
    {
        return $amount / $this->getRate($currency);
    }
    
    /**
     * Converts an amount in EUR to the specified currency using the fixed rate.
     *
     * @param float  $amount Amount in EUR.
     * @param string $currency
     * @return float
     */
    public function convertFromEuro(float $amount, string $currency): float
    {
        return $amount * $this->getRate($currency);
    }
}
