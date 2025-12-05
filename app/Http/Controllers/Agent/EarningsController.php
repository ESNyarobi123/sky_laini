<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $agent = $request->user()->agent;
        
        // Ensure agent has a wallet
        if (!$agent->wallet) {
            $agent->wallet()->create(['balance' => 0]);
            $agent->refresh();
        }

        $wallet = $agent->wallet;
        $transactions = $wallet->transactions()->latest()->paginate(15);

        return view('agent.earnings.index', compact('wallet', 'transactions'));
    }
}
