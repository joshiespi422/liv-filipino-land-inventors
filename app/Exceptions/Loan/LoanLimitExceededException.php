<?php

namespace App\Exceptions\Loan;

use Exception;

class LoanLimitExceededException extends Exception
{
    public function __construct(
        public float $limit
    ) {
        parent::__construct('Loan limit exceeded.');
    }
}
