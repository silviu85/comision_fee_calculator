<?php

namespace App;

interface CommissionCalculatorInterface
{
    /**
     * Calculates the commission fee for the given Operation.
     */
    public function calculate(Operation $operation): float;
}
?>