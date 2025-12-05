<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with(['agent.user'])
            ->latest()
            ->paginate(15);
            
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function approve(Withdrawal $withdrawal)
    {
        $withdrawal->update(['status' => 'approved']);
        return back()->with('success', 'Withdrawal approved successfully.');
    }

    public function reject(Withdrawal $withdrawal)
    {
        $withdrawal->update(['status' => 'rejected']);
        return back()->with('success', 'Withdrawal rejected.');
    }
}
