<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Display the referral page for authenticated user (Customer or Agent)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure user has a referral code
        $referralCode = $user->ensureReferralCode();
        
        // Get referral statistics
        $stats = $this->referralService->getUserStats($user);
        
        // Get referral history
        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,profile_picture,created_at,role')
            ->orderByDesc('created_at')
            ->get();
        
        // Get share messages
        $shareMessage = $this->referralService->getShareMessage($user);
        
        // Check if user was referred and has pending discount
        $pendingDiscount = $this->referralService->hasUnusedDiscount($user);
        
        // Get leaderboard (top 5)
        $leaderboard = $this->referralService->getLeaderboard(5);
        
        // Get bonus amounts from settings
        $bonusSettings = [
            'customer_referral_bonus' => Referral::getSetting('customer_referral_bonus', 500),
            'customer_referred_discount' => Referral::getSetting('customer_referred_discount', 300),
            'agent_referral_bonus' => Referral::getSetting('agent_referral_bonus', 1000),
            'agent_referred_bonus' => Referral::getSetting('agent_referred_bonus', 500),
        ];
        
        return view('referrals.index', compact(
            'user',
            'referralCode',
            'stats',
            'referrals',
            'shareMessage',
            'pendingDiscount',
            'leaderboard',
            'bonusSettings'
        ));
    }
}
