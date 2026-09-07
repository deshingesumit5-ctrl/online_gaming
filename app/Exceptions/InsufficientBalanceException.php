<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected int $availableBalance;
    protected int $requiredAmount;

    public function __construct(string $message = 'Insufficient wallet points balance.', int $availableBalance = 0, int $requiredAmount = 0, int $code = 422)
    {
        parent::__construct($message, $code);
        $this->availableBalance = $availableBalance;
        $this->requiredAmount = $requiredAmount;
    }

    public function getAvailableBalance(): int
    {
        return $this->availableBalance;
    }

    public function getRequiredAmount(): int
    {
        return $this->requiredAmount;
    }
}
