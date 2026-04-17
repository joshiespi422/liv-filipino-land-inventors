<?php

namespace App\Http\Controllers\API\Wallet;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Wallet\ApiWalletResource;
use App\Http\Resources\Api\Wallet\ApiWalletTransactionResource;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService)
    {
    }

    /**
     * Get Wallet Overview.
     *
     * Returns the current balance and basic details of the user's wallet.
     *
     * @tags Wallet
     */
    public function index(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getUserWallet($request->user());

        return (new ApiWalletResource($wallet))
            ->response();
    }

   public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $this->walletService->getUserWallet($user);
        $wallet->show = !$wallet->show;
        $wallet->save();

        return (new ApiWalletResource($wallet))->response();
    }

    /**
     * Get Wallet Transactions.
     *
     * Returns a paginated list of transactions for the user's wallet.
     *
     * @tags Wallet
     */
    public function transaction(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getUserWallet($request->user());
        $transactions = $this->walletService->getWalletTransactions($wallet);

        return ApiWalletTransactionResource::collection($transactions)
            ->response();
    }
}
