<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\LineRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add commission to agent's wallet after completing a request.
     */
    public function addCommission(Agent $agent, LineRequest $request, float $commission): WalletTransaction
    {
        $wallet = $agent->wallet ?? $this->createWallet($agent);

        return DB::transaction(function () use ($wallet, $agent, $request, $commission) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $commission;

            $wallet->increment('balance', $commission);
            $agent->increment('total_earnings', $commission);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'line_request_id' => $request->id,
                'transaction_type' => 'commission',
                'amount' => $commission,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Commission for request #{$request->request_number}",
                'reference' => "COMM-{$request->id}",
            ]);
        });
    }

    /**
     * Create a wallet for an agent.
     */
    public function createWallet(Agent $agent): Wallet
    {
        return Wallet::create([
            'agent_id' => $agent->id,
            'balance' => 0.00,
            'pending_balance' => 0.00,
            'currency' => 'TZS',
        ]);
    }

    /**
     * Process withdrawal request.
     */
    public function processWithdrawal(Agent $agent, float $amount, string $paymentMethod, string $accountNumber): bool
    {
        $wallet = $agent->wallet;

        if (!$wallet || $wallet->balance < $amount) {
            return false;
        }

        return DB::transaction(function () use ($wallet, $agent, $amount, $paymentMethod, $accountNumber) {
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->decrement('balance', $amount);
            $wallet->increment('pending_balance', $amount);

            $agent->withdrawals()->create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'account_number' => $accountNumber,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_type' => 'withdrawal',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Withdrawal request via {$paymentMethod}",
                'reference' => "WD-".time(),
            ]);

            return true;
        });
    }
}
