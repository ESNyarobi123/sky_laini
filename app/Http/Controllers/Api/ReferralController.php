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
     * Get user's referral information and stats
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->referralService->getUserStats($user);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get referral code (generate if doesn't exist)
     */
    public function getCode(Request $request): JsonResponse
    {
        $user = $request->user();
        $code = $this->referralService->generateReferralCode($user);

        return response()->json([
            'success' => true,
            'code' => $code,
            'share_message' => $this->referralService->getShareMessage($user),
        ]);
    }

    /**
     * Get user's referral history
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,profile_picture,created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($referral) {
                return [
                    'id' => $referral->id,
                    'referred_name' => $referral->referred->name,
                    'referred_profile' => $referral->referred->profile_picture,
                    'referred_type' => $referral->referred_type,
                    'bonus_amount' => $referral->bonus_amount,
                    'status' => $referral->status,
                    'status_label' => match ($referral->status) {
                        'pending' => 'Anasubiri',
                        'completed' => 'Amekamilika',
                        'rewarded' => 'Umelipwa',
                        default => $referral->status,
                    },
                    'created_at' => $referral->created_at->diffForHumans(),
                    'rewarded_at' => $referral->rewarded_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'referrals' => $referrals,
            'total' => $referrals->count(),
        ]);
    }

    /**
     * Validate a referral code
     */
    public function validateCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $code = strtoupper($request->code);
        $referrer = \App\Models\User::where('referral_code', $code)->first();

        if (!$referrer) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Code hii haipo au siyo sahihi.',
            ]);
        }

        // Can't use own code
        if ($request->user() && $referrer->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Huwezi kutumia code yako mwenyewe.',
            ]);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'referrer_name' => $referrer->name,
            'discount' => Referral::getReferredBonus('customer'),
            'message' => "Code sahihi! Utapata discount ya TSh " . number_format(Referral::getReferredBonus('customer')) . " kwenye order yako ya kwanza.",
        ]);
    }

    /**
     * Get referral leaderboard
     */
    public function leaderboard(): JsonResponse
    {
        $leaderboard = $this->referralService->getLeaderboard(20);

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Check if current user has unused referral discount
     */
    public function checkDiscount(Request $request): JsonResponse
    {
        $user = $request->user();
        $discount = $this->referralService->hasUnusedDiscount($user);

        return response()->json([
            'success' => true,
            'has_discount' => $discount !== null,
            'discount_amount' => $discount ?? 0,
        ]);
    }
}
