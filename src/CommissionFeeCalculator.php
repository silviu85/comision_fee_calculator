<?php

namespace App;

class CommissionFeeCalculator
{
    private CurrencyConverter $converter;
    private DepositCommissionCalculator $depositCalc;
    private BusinessWithdrawCommissionCalculator $businessWithdrawCalc;
    private PrivateWithdrawCommissionCalculator $privateWithdrawCalc;

    public function __construct(CurrencyConverter $converter = null)
    {
        // Instantiate shared services.
        $this->converter = $converter ?? new CurrencyConverter();
        $this->depositCalc = new DepositCommissionCalculator($this->converter);
        $this->businessWithdrawCalc = new BusinessWithdrawCommissionCalculator($this->converter);
        $this->privateWithdrawCalc = new PrivateWithdrawCommissionCalculator($this->converter);
    }

    /**
     * Selects the proper calculator based on Operation type and user type.
     */
    public function calculate(Operation $operation): float
    {
        if ($operation->type === 'deposit') {
            return $this->depositCalc->calculate($operation);
        }
        
        if ($operation->type === 'withdraw') {
            if ($operation->userType === 'business') {
                return $this->businessWithdrawCalc->calculate($operation);
            }
            if ($operation->userType === 'private') {
                return $this->privateWithdrawCalc->calculate($operation);
            }
        }

        throw new \Exception("Unsupported operation or user type: Type [{$operation->type}], User Type [{$operation->userType}]");
    }
}
