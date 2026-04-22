<?php

namespace App\Exceptions\IntellectualProperty;

use Exception;

class IntellectualPropertyNotReadyForPaymentException extends Exception
{
    public function __construct(public readonly int $statusId)
    {
        parent::__construct('Intellectual property is not ready for payment.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
