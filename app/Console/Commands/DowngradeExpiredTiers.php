<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\User;
use App\Enums\UserTier;
use Illuminate\Support\Facades\Log;

#[Signature('titipin:downgrade-expired-tiers')]
#[Description('Downgrade premium users to basic if their subscription has expired')]
class DowngradeExpiredTiers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $expiredUsersCount = User::whereNotNull('tier_expired_at')
            ->where('tier_expired_at', '<=', $now)
            ->update([
                'tier' => UserTier::BASIC,
                'boost_quota' => 0,
                'tier_expired_at' => null,
            ]);

        if ($expiredUsersCount > 0) {
            Log::info("Scheduler Executed: Downgraded {$expiredUsersCount} users to BASIC tier.");
            $this->info("Successfully downgraded {$expiredUsersCount} expired users to BASIC.");
        } else {
            $this->info("All clear, no expired tiers today.");
        }
    }
}