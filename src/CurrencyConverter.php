<?php

namespace App;

class CurrencyConverter
{
    /**
     * Holds the conversion rates fetched from the API.
     * They are keyed by currency code.
     */
    private array $rates = [];
    /**
     * The api link with the access key.
     * I can modify this in case we need to use another API
    */
    private const APIURL = 'https://api.exchangeratesapi.io/latest?access_key=f7768815d2c522e1d62a564df9686763';

    /**
     * List of currencies that use zero decimals.
     * (In real-world cases, you might obtain this information from an external source.)
     */
    private array $zeroDecimalCurrencies = ['JPY', 'HUF', 'CLP', 'ISK'];

    /**
     * Constructor.
     *
     * Immediately fetches live rates from the API.
     *
     * @throws \Exception if live rates cannot be fetched.
     */
    public function __construct()
    {
        $this->fetchLiveRates();
    }

    /**
     * Connects to the external API and retrieves the latest conversion rates.
     *
     * Expected API Response Format (example):
     * {
     *   "success": true,
     *   "timestamp": 1742572143,
     *   "base": "EUR",
     *   "date": "2025-03-21",
     *   "rates": {
     *       "AED": 3.975141,
     *       "AFN": 76.43082,
     *       ... etc ...
     *   }
     * }
     *
     * @throws \Exception when the API call fails or the response is invalid.
     */
    private function fetchLiveRates(): void
    {
        $apiUrl = self::APIURL;
        try {
            $json = file_get_contents($apiUrl);
            if ($json === false) {
                throw new \Exception("Error retrieving rates from API.");
            }
            $data = json_decode($json, true);
            if (!isset($data['success']) || $data['success'] !== true) {
                throw new \Exception("API did not return a success response.");
            }
            if (!isset($data['rates']) || !is_array($data['rates'])) {
                throw new \Exception("Invalid API response: missing or invalid 'rates'.");
            }
            // The API returns rates relative to the base. We include the base currency at rate 1.
            $this->rates = $data['rates'];
            $base = $data['base'] ?? 'EUR';
            $this->rates[$base] = 1.0;
        } catch (\Exception $e) {
            // In this design we throw an exception (or you could choose to implement a fallback mechanism).
            throw new \Exception("Failed to fetch live rates: " . $e->getMessage());
        }
    }

    /**
     * Returns the conversion rate for the given currency.
     *
     * @param string $currency The currency code (e.g., "USD", "JPY")
     * @return float
     * @throws \Exception if the currency is not available.
     */
    public function getRate(string $currency): float
    {
        if (!isset($this->rates[$currency])) {
            throw new \Exception("Unknown currency: $currency");
        }
        return $this->rates[$currency];
    }

    /**
     * Returns the number of decimal places to use for a given currency.
     *
     * By default, we assume 2 decimals unless the currency is in the zero-decimal list.
     *
     * @param string $currency
     * @return int
     */
    public function getDecimals(string $currency): int
    {
        if (in_array(strtoupper($currency), $this->zeroDecimalCurrencies, true)) {
            return 0;
        }
        return 2;
    }

    /**
     * Converts an amount from the specified currency to EUR.
     *
     * @param float $amount
     * @param string $currency
     * @return float The converted amount in EUR.
     */
    public function convertToEuro(float $amount, string $currency): float
    {
        $rate = $this->getRate($currency);
        return $amount / $rate;
    }

    /**
     * Converts an amount from EUR to the specified currency.
     *
     * @param float $amount The amount in EUR.
     * @param string $currency
     * @return float The converted amount in the target currency.
     */
    public function convertFromEuro(float $amount, string $currency): float
    {
        $rate = $this->getRate($currency);
        return $amount * $rate;
    }
}
