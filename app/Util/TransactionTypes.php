<?php

namespace App\Util;

class TransactionTypes
{
    const DEPOSIT = 'deposit';
    const TRANSFER = 'transfer';

    /**
     * Get all transaction types.
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::DEPOSIT,
            self::TRANSFER,
        ];
    }
}
