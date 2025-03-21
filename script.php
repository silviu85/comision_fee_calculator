<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\OperationParser;
use App\CommissionFeeCalculator;
use App\CurrencyConverter;

// Check for input file argument.
if ($argc < 2) {
    echo "Usage: php script.php input.csv\n";
    exit(1);
}

$filePath = $argv[1];

try {
    $parser = new OperationParser();
    $operations = $parser->parse($filePath);

    $calculator = new CommissionFeeCalculator();
    $converter  = new CurrencyConverter();

    foreach ($operations as $operation) {
        $fee = $calculator->calculate($operation);
        $decimals = $converter->getDecimals($operation->currency);
        // Format fee with proper decimal places (e.g. for EUR and USD: 2 decimals, JPY: integer)
        echo number_format($fee, $decimals, '.', '') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>