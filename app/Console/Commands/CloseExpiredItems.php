<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('titipin:close-expired')]
#[Description('Automatically close posts or requests that have passed their deadline')]
class CloseExpiredItems extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $jastipListings = DB::table('jastip_listings')
            ->where('status', 'ACTIVE')
            ->where('deadline', '<', $now)
            ->update(['status' => 'CLOSED']);

        $jastipRequests = DB::table('jastip_requests')
            ->where('status', 'OPEN')
            ->where('created_at', '<', $now->copy()->subHours(24))
            ->update(['status' => 'CLOSED']);

        $prelovedRequests = DB::table('preloved_requests')
            ->where('status', 'OPEN')
            ->where('created_at', '<', $now->copy()->subHours(24))
            ->update(['status' => 'CLOSED']);

        $prelovedListings = DB::table('preloved_listings')
            ->where('status', 'AVAILABLE')
            ->where('created_at', '<', $now->copy()->subDays(7))
            ->update(['status' => 'CLOSED']);

        if ($jastipListings || $jastipRequests || $prelovedRequests || $prelovedListings) {
            Log::info("Scheduler Executed: Closed {$jastipListings} JL, {$jastipRequests} JR, {$prelovedRequests} PR, {$prelovedListings} PL");
            
            $this->info("Successfully closed: {$jastipListings} JL, {$jastipRequests} JR, {$prelovedRequests} PR, {$prelovedListings} PL");
        } else {
            $this->line("All clear, no expired items at the moment.");
        }
    }
}