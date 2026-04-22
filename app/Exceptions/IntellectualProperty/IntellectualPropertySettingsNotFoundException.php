<?php

namespace App\Exceptions\IntellectualProperty;

use Exception;

class IntellectualPropertySettingsNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct('No settings found for this intellectual property.');
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 404);
    }
}
