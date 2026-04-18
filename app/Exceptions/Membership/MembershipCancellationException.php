<?php

namespace App\Exceptions\Membership;

use Exception;

class MembershipCancellationException extends Exception
{
    public function __construct(
        string $message = 'Cannot cancel this membership.',
    ) {
        parent::__construct($message);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 422);
    }
}
