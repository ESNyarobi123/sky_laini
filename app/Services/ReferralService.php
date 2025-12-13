<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Generate a referral code for a user
     */
    public function generateReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        $code = Referral::generateCode();
        $user->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Apply a referral code during registration
     */
    public function applyReferralCode(User $newUser, string $referralCode): ?Referral
    {
        try {
            // Find the referrer
            $referrer = User::where('referral_code', strtoupper($referralCode))->first();

            if (!$referrer || $referrer->id === $newUser->id) {
                return null;
            }

            // Determine types
            $referrerType = $referrer->role?->value ?? 'customer';
            $referredType = $newUser->role?->value ?? 'customer';

            // Calculate bonuses
            $bonusAmount = Referral::getReferrerBonus($referrerType);
            $discountAmount = Referral::getReferredBonus($referredType);

            // Create referral record
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'referral_code' => $referralCode,
                'referrer_type' => $referrerType,
                'referred_type' => $referredType,
                'bonus_amount' => $bonusAmount,
                'discount_amount' => $discountAmount,
                'status' => 'pending',
            ]);

            // Update user's referred_by
            $newUser->update(['referred_by' => $referrer->id]);

            // Increment referrer's count
            $referrer->increment('referral_count');

            Log::info('Referral applied', [
                'referrer_id' => $referrer->id,
                'referred_id' => $newUser->id,
                'code' => $referralCode,
            ]);

            return $referral;
        } catch (\Exception $e) {
            Log::error('Failed to apply referral code', [
                'code' => $referralCode,
                'user_id' => $newUser->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Complete a referral after first transaction
     */
    public function completeReferral(User $user): bool
    {
        try {
            $referral = Referral::where('referred_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$referral) {
                return false;
            }

            DB::transaction(function () use ($referral) {
                // Mark as completed
                $referral->markAsCompleted();

                // Process rewards
                $this->processReferralRewards($referral);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to complete referral', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process rewards for both referrer and referred
     */
    protected function processReferralRewards(Referral $referral): void
    {
        $referrer = $referral->referrer;
        $referred = $referral->referred;

        // Reward the referrer
        if ($referral->bonus_amount > 0) {
            $this->creditReferralBonus(
                $referrer,
                $referral->bonus_amount,
                "Bonus ya referral kutoka {$referred->name}"
            );

            $referrer->increment('referral_earnings', $referral->bonus_amount);
        }

        // Credit the referred user (for agents, add to wallet)
        if ($referral->referred_type === 'agent' && $referral->discount_amount > 0) {
            $this->creditReferralBonus(
                $referred,
                $referral->discount_amount,
                "Bonus ya kujiunga kupitia referral"
            );
        }

        // Mark as rewarded
        $referral->markAsRewarded();

        // Send notifications
        $this->sendReferralNotifications($referral);
    }

    /**
     * Credit referral bonus to user's wallet
     */
    protected function creditReferralBonus(User $user, float $amount, string $description): void
    {
        // For agents, credit to their wallet
        if ($user->agent && $user->agent->wallet) {
            $wallet = $user->agent->wallet;
            $wallet->increment('balance', $amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description,
                'reference' => 'REF-' . strtoupper(uniqid()),
            ]);
        }

        Log::info('Referral bonus credited', [
            'user_id' => $user->id,
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Send notifications about referral rewards
     */
    protected function sendReferralNotifications(Referral $referral): void
    {
        $notificationService = app(InAppNotificationService::class);

        // Notify referrer
        $notificationService->send(
            $referral->referrer,
            'referral_reward',
            'Hongera! Umepata Bonus! 🎉',
            "Umepata TSh " . number_format($referral->bonus_amount) . " kwa kumwalika {$referral->referred->name}!",
            ['referral_id' => $referral->id]
        );

        // Notify referred (if agent)
        if ($referral->referred_type === 'agent' && $referral->discount_amount > 0) {
            $notificationService->send(
                $referral->referred,
                'referral_bonus',
                'Bonus ya Kujiunga! 🎁',
                "Umepata TSh " . number_format($referral->discount_amount) . " kama bonus ya kujiunga kupitia referral!",
                ['referral_id' => $referral->id]
            );
        }
    }

    /**
     * Get referral statistics for a user
     */
    public function getUserStats(User $user): array
    {
        $referrals = Referral::where('referrer_id', $user->id)->get();

        return [
            'referral_code' => $user->referral_code ?? $this->generateReferralCode($user),
            'total_referrals' => $referrals->count(),
            'pending_referrals' => $referrals->where('status', 'pending')->count(),
            'completed_referrals' => $referrals->whereIn('status', ['completed', 'rewarded'])->count(),
            'total_earnings' => $referrals->where('status', 'rewarded')->sum('bonus_amount'),
            'share_message' => $this->getShareMessage($user),
        ];
    }

    /**
     * Get share message for social sharing
     */
    public function getShareMessage(User $user): array
    {
        $code = $user->referral_code ?? $this->generateReferralCode($user);
        $bonus = ReferralSetting::getValue('customer_referred_discount', 300);

        $message = "Jiunge na Sky Laini na upate TSh {$bonus} discount! " .
                   "Tumia code yangu: {$code} wakati wa kujisajili. " .
                   "Pata simu yako isajiliwe haraka! 📱✨";

        return [
            'text' => $message,
            'whatsapp_url' => "https://wa.me/?text=" . urlencode($message),
            'sms_body' => $message,
            'code' => $code,
        ];
    }

    /**
     * Get leaderboard of top referrers
     */
    public function getLeaderboard(int $limit = 10): array
    {
        return User::where('referral_count', '>', 0)
            ->orderByDesc('referral_count')
            ->orderByDesc('referral_earnings')
            ->take($limit)
            ->get()
            ->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $user->name,
                    'profile_picture' => $user->profile_picture,
                    'referral_count' => $user->referral_count,
                    'total_earnings' => $user->referral_earnings,
                    'role' => $user->role?->value ?? 'customer',
                ];
            })
            ->toArray();
    }

    /**
     * Check if user has unused referral discount
     */
    public function hasUnusedDiscount(User $user): ?float
    {
        $referral = Referral::where('referred_id', $user->id)
            ->where('status', 'pending')
            ->where('referred_type', 'customer')
            ->first();

        return $referral ? $referral->discount_amount : null;
    }

    /**
     * Apply referral discount to a line request
     */
    public function applyDiscount(User $user, float $originalAmount): array
    {
        $discount = $this->hasUnusedDiscount($user);

        if (!$discount) {
            return [
                'original_amount' => $originalAmount,
                'discount' => 0,
                'final_amount' => $originalAmount,
                'has_discount' => false,
            ];
        }

        $finalAmount = max(0, $originalAmount - $discount);

        return [
            'original_amount' => $originalAmount,
            'discount' => $discount,
            'final_amount' => $finalAmount,
            'has_discount' => true,
        ];
    }
}
