<?php

namespace App;

class OperationParser
{
    /**
     * Reads CSV file and returns an array of Operation objects.
     *
     * @param string $filePath
     * @return Operation[]
     * @throws \Exception if the file cannot be found
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: " . $filePath);
        }

        $operations = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                // Expect exactly 6 columns per line.
                if (count($data) < 6) {
                    continue;
                }
                [$date, $userId, $userType, $type, $amount, $currency] = $data;

                try {
                    $operation = new Operation(
                        new \DateTime($date),
                        (int)$userId,
                        trim($userType),
                        trim($type),
                        (float)$amount,
                        trim($currency)
                    );
                    $operations[] = $operation;
                } catch (\Exception $e) {
                    // You might want to log or handle individual parsing errors.
                    continue;
                }
            }
            fclose($handle);
        }

        return $operations;
    }
}
