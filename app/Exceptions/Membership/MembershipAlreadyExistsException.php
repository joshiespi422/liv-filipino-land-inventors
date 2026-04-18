<?php

namespace App\Exceptions\Membership;

use Exception;

class MembershipAlreadyExistsException extends Exception
{
    public function __construct(
        string $message = 'You already have a pending or active membership.',
    ) {
        parent::__construct($message);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], 409);
    }
}
