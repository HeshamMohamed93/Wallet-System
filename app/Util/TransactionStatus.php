<?php

namespace App\Util;

class TransactionStatus
{
    const DEPOSIT = 'deposit';
    const RECEIVED = 'received';
    const SENT = 'sent';
    const UNKNOWN = 'unknown';

    /**
     * Get all transaction types.
     *
     * @return array
     */
    public static function all(): array
    {
        return [
            self::DEPOSIT,
            self::RECEIVED,
            self::SENT,
            self::UNKNOWN,
        ];
    }
}
