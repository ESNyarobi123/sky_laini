<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    protected ReferralService $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    /**
     * Get referral overview
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Ensure user has a referral code
        $user->ensureReferralCode();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,role,profile_picture')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $stats = [
            'total_referrals' => Referral::where('referrer_id', $user->id)->count(),
            'completed_referrals' => Referral::where('referrer_id', $user->id)->where('status', 'completed')->count(),
            'pending_referrals' => Referral::where('referrer_id', $user->id)->where('status', 'pending')->count(),
            'total_earnings' => $user->referral_earnings ?? 0,
        ];

        return response()->json([
            'referral_code' => $user->referral_code,
            'share_url' => url('/register?ref=' . $user->referral_code),
            'share_message' => $this->getShareMessage($user),
            'stats' => $stats,
            'recent_referrals' => $referrals->map(fn($r) => $this->formatReferral($r)),
        ]);
    }

    /**
     * Get user's referral code
     */
    public function getMyCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = $user->ensureReferralCode();

        return response()->json([
            'code' => $code,
            'share_url' => url('/register?ref=' . $code),
            'share_message' => $this->getShareMessage($user),
            'bonus_amount' => $user->isAgent() 
                ? config('referral.agent_bonus', 1000) 
                : config('referral.customer_bonus', 500),
        ]);
    }

    /**
     * Get referral statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalReferrals = Referral::where('referrer_id', $user->id)->count();
        $completedReferrals = Referral::where('referrer_id', $user->id)->where('status', 'completed')->count();
        $pendingReferrals = Referral::where('referrer_id', $user->id)->where('status', 'pending')->count();
        
        $totalEarnings = $user->referral_earnings ?? 0;

        // Monthly breakdown
        $monthlyStats = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count, SUM(bonus_amount) as earnings')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return response()->json([
            'total_referrals' => $totalReferrals,
            'completed_referrals' => $completedReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_earnings' => $totalEarnings,
            'total_earnings_formatted' => 'TSh ' . number_format($totalEarnings),
            'monthly_stats' => $monthlyStats,
        ]);
    }

    /**
     * Get referral history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $request->query('status'); // pending, completed, all

        $query = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,role,profile_picture');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $referrals = $query->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'referrals' => $referrals->map(fn($r) => $this->formatReferral($r)),
            'pagination' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'total' => $referrals->total(),
            ],
        ]);
    }

    /**
     * Check if user has unused discount (for customers)
     */
    public function checkDiscount(Request $request): JsonResponse
    {
        $user = $request->user();

        $hasDiscount = $this->referralService->hasUnusedDiscount($user);
        
        $discountReferral = null;
        if ($hasDiscount) {
            $discountReferral = Referral::where('referred_id', $user->id)
                ->where('status', 'pending')
                ->first();
        }

        return response()->json([
            'has_discount' => $hasDiscount,
            'discount_amount' => $discountReferral?->discount_amount ?? 0,
            'discount_amount_formatted' => $discountReferral 
                ? 'TSh ' . number_format($discountReferral->discount_amount) 
                : null,
            'message' => $hasDiscount 
                ? 'Una discount ya TSh ' . number_format($discountReferral->discount_amount) . ' kwenye order yako!'
                : null,
        ]);
    }

    /**
     * Get referral earnings (for agents)
     */
    public function earnings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get completed referrals with earnings
        $earnings = Referral::where('referrer_id', $user->id)
            ->where('status', 'completed')
            ->whereNotNull('rewarded_at')
            ->with('referred:id,name,profile_picture')
            ->orderByDesc('rewarded_at')
            ->get();

        $totalEarnings = $earnings->sum('bonus_amount');

        return response()->json([
            'total_earnings' => $totalEarnings,
            'total_earnings_formatted' => 'TSh ' . number_format($totalEarnings),
            'earnings_count' => $earnings->count(),
            'earnings' => $earnings->map(fn($r) => [
                'id' => $r->id,
                'amount' => $r->bonus_amount,
                'amount_formatted' => 'TSh ' . number_format($r->bonus_amount),
                'referred_name' => $r->referred?->name ?? 'User',
                'referred_type' => $r->referred_type,
                'rewarded_at' => $r->rewarded_at?->format('d M Y'),
                'rewarded_at_relative' => $r->rewarded_at?->diffForHumans(),
            ]),
        ]);
    }

    // ==================== HELPERS ====================

    /**
     * Format referral for API response
     */
    private function formatReferral(Referral $referral): array
    {
        return [
            'id' => $referral->id,
            'referred' => [
                'id' => $referral->referred?->id,
                'name' => $referral->referred?->name ?? 'User',
                'profile_picture' => $referral->referred?->profile_picture,
            ],
            'referred_type' => $referral->referred_type,
            'status' => $referral->status,
            'bonus_amount' => $referral->bonus_amount,
            'bonus_amount_formatted' => 'TSh ' . number_format($referral->bonus_amount),
            'discount_amount' => $referral->discount_amount,
            'created_at' => $referral->created_at->format('d M Y'),
            'created_at_relative' => $referral->created_at->diffForHumans(),
            'completed_at' => $referral->completed_at?->format('d M Y'),
        ];
    }

    /**
     * Generate share message
     */
    private function getShareMessage($user): string
    {
        $code = $user->referral_code;
        $bonus = $user->isAgent() ? 1000 : 500;

        return "Jisajili Sky Laini na upate TSh {$bonus} discount! Tumia code yangu: {$code}\n\n" .
               "Bonyeza hapa kusajili: " . url('/register?ref=' . $code);
    }
}
