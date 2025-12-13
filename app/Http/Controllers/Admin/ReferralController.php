<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\ReferralSetting;
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
     * Display referral management dashboard
     */
    public function index()
    {
        $stats = [
            'total_referrals' => Referral::count(),
            'pending_referrals' => Referral::where('status', 'pending')->count(),
            'completed_referrals' => Referral::where('status', 'completed')->count(),
            'rewarded_referrals' => Referral::where('status', 'rewarded')->count(),
            'total_bonuses_paid' => Referral::where('status', 'rewarded')->sum('bonus_amount'),
        ];

        $recentReferrals = Referral::with(['referrer', 'referred'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $topReferrers = User::where('referral_count', '>', 0)
            ->orderByDesc('referral_count')
            ->take(10)
            ->get();

        $settings = ReferralSetting::all()->pluck('value', 'key');

        return view('admin.referrals.index', compact('stats', 'recentReferrals', 'topReferrers', 'settings'));
    }

    /**
     * Display referral settings page
     */
    public function settings()
    {
        $settings = ReferralSetting::all();

        return view('admin.referrals.settings', compact('settings'));
    }

    /**
     * Update referral settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'customer_referral_bonus' => 'required|numeric|min:0',
            'customer_referred_discount' => 'required|numeric|min:0',
            'agent_referral_bonus' => 'required|numeric|min:0',
            'agent_referred_bonus' => 'required|numeric|min:0',
            'min_jobs_for_reward' => 'required|integer|min:1',
        ]);

        foreach ($validated as $key => $value) {
            ReferralSetting::setValue($key, $value);
        }

        return back()->with('success', 'Referral settings updated successfully!');
    }

    /**
     * Display referral leaderboard
     */
    public function leaderboard()
    {
        $leaderboard = $this->referralService->getLeaderboard(50);

        return view('admin.referrals.leaderboard', compact('leaderboard'));
    }
}
