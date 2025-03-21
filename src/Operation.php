<?php

namespace App;

class Operation
{
    public \DateTime $date;
    public int $userId;
    public string $userType; // 'private' or 'business'
    public string $type;     // 'deposit' or 'withdraw'
    public float $amount;
    public string $currency;

    public function __construct(\DateTime $date, int $userId, string $userType, string $type, float $amount, string $currency)
    {
        $this->date     = $date;
        $this->userId   = $userId;
        $this->userType = $userType;
        $this->type     = $type;
        $this->amount   = $amount;
        $this->currency = $currency;
    }
}
