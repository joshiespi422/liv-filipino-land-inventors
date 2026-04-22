<?php

namespace App\Exceptions\IntellectualProperty;

use Exception;

class IntellectualPropertyAlreadyAppliedPaymentSettingsException extends Exception
{
    public function __construct()
    {
        parent::__construct('Payment settings has already been applied for this intellectual property.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
