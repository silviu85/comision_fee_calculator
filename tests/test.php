<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\OperationParser;
use App\CommissionFeeCalculator;
use App\TestCurrencyConverter;

$inputCsv = <<<CSV
2014-12-31,4,private,withdraw,1200.00,EUR
2015-01-01,4,private,withdraw,1000.00,EUR
2016-01-05,4,private,withdraw,1000.00,EUR
2016-01-05,1,private,deposit,200.00,EUR
2016-01-06,2,business,withdraw,300.00,EUR
2016-01-06,1,private,withdraw,30000,JPY
2016-01-07,1,private,withdraw,1000.00,EUR
2016-01-07,1,private,withdraw,100.00,USD
2016-01-10,1,private,withdraw,100.00,EUR
2016-01-10,2,business,deposit,10000.00,EUR
2016-01-10,3,private,withdraw,1000.00,EUR
2016-02-15,1,private,withdraw,300.00,EUR
2016-02-19,5,private,withdraw,3000000,JPY
CSV;

$expectedOutput = <<<OUTPUT
0.60
3.00
0.00
0.06
1.50
0
0.70
0.30
0.30
3.00
0.00
0.00
8612
OUTPUT;

$tempFile = tempnam(sys_get_temp_dir(), 'commission_');
file_put_contents($tempFile, $inputCsv);

$parser = new OperationParser();
$operations = $parser->parse($tempFile);

// Use the test-specific currency converter.
$testCurrencyConverter = new TestCurrencyConverter();
$calculator = new CommissionFeeCalculator($testCurrencyConverter);

$result = [];
foreach ($operations as $operation) {
    $fee = $calculator->calculate($operation);
    $decimals = $testCurrencyConverter->getDecimals($operation->currency);
    $result[] = number_format($fee, $decimals, '.', '');
}

unlink($tempFile);

$output = implode("\n", $result);

// After generating $output from the calculations:
$expectedNormalized = str_replace("\r", '', trim($expectedOutput));
$outputNormalized   = str_replace("\r", '', trim($output));

if ($outputNormalized === $expectedNormalized) {
    echo "Test passed!\n";
    exit(0);
} else {
    echo "Test failed.\nExpected output:\n" . $expectedNormalized . "\n\nGot:\n" . $outputNormalized . "\n";
    exit(1);
}

?>