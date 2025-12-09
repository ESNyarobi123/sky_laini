<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get wallet balance and transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $agent = $request->user()->agent;

        if (!$agent) {
            return response()->json(['message' => 'Agent profile not found'], 404);
        }

        // Ensure wallet exists
        if (!$agent->wallet) {
            $agent->wallet()->create(['balance' => 0]);
            $agent->refresh();
        }

        $wallet = $agent->wallet;
        $transactions = $wallet->transactions()->latest()->paginate(15);

        return response()->json([
            'wallet' => $wallet,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Request a withdrawal.
     */
    public function withdraw(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'method' => 'required|in:mobile_money,bank',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
        ]);

        $agent = $request->user()->agent;

        if (!$agent || !$agent->wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        if ($agent->wallet->balance < $validated['amount']) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        // Create withdrawal request
        $withdrawal = Withdrawal::create([
            'agent_id' => $agent->id,
            'wallet_id' => $agent->wallet->id,
            'amount' => $validated['amount'],
            'payment_method' => $validated['method'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'status' => 'pending',
        ]);

        // Deduct from wallet (or hold it)
        // Usually we deduct immediately or mark as pending. 
        // For simplicity, let's deduct and if rejected, refund.
        $agent->wallet->decrement('balance', $validated['amount']);

        $agent->wallet->transactions()->create([
            'transaction_type' => 'debit',
            'amount' => $validated['amount'],
            'balance_before' => $agent->wallet->balance + $validated['amount'],
            'balance_after' => $agent->wallet->balance,
            'description' => 'Withdrawal Request #' . $withdrawal->id,
        ]);

        return response()->json($withdrawal, 201);
    }
}
