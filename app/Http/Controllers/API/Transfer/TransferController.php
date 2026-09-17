<?php

namespace App\Http\Controllers\API\Transfer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\TransferRequest;
use App\Http\Resources\Api\Transfer\ApiBatchTransferResource;
use App\Http\Resources\Api\Wallet\ApiWalletResource;
use App\Services\Transfer\TransferService;
use App\Services\Wallet\WalletService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function __construct(
        protected TransferService $transferService,
        protected WalletService $walletService,
    ) {}

    public function resolveQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_payload' => [
                'required',
                'string',
                'max:10000',
            ],
        ]);

        try {
            $result = $this->transferService->resolveQr(
                $request->string('qr_payload')->toString()
            );

            return response()->json([
                'success' => true,
                'message' => 'QR code resolved successfully.',
                'data' => $result,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function store(TransferRequest $request): JsonResponse
    {
        try {
            $batchTransfer = $this->transferService->transfer($request->user(), $request->validated());
            $wallet = $this->walletService->getUserWallet($request->user()->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Transfer submitted successfully.',
                'data' => new ApiBatchTransferResource($batchTransfer),
                'wallet' => new ApiWalletResource($wallet),
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function status(Request $request, string $reference): JsonResponse
    {
        $batchTransfer = $request->user()->wallet
            ->batchTransfers()
            ->where('reference_number', $reference)
            ->firstOrFail();

        return response()->json([
            'data' => new ApiBatchTransferResource($batchTransfer),
        ]);
    }
}
