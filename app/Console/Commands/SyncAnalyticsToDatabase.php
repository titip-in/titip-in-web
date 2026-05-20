<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\JastipListing;
use App\Models\JastipRequest;
use App\Models\PrelovedListing;
use App\Models\PrelovedRequest;

#[Signature('analytics:sync')]
#[Description('Sync views and clicks from Redis to PostgreSQL and clear Redis keys')]
class SyncAnalyticsToDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = [
            'jastip_listing' => JastipListing::class,
            'jastip_request' => JastipRequest::class,
            'preloved_listing' => PrelovedListing::class,
            'preloved_request' => PrelovedRequest::class,
        ];

        $metrics = ['views', 'clicks'];

        foreach ($metrics as $metric) {
            $keys = Redis::keys("{$metric}:*:*");

            foreach ($keys as $fullKey) {
                $key = str_replace(config('database.redis.options.prefix'), '', $fullKey);
                $parts = explode(':', $key);

                if (count($parts) >= 3) {
                    $type = $parts[1];
                    $id = $parts[2];
                    
                    $val = (int) Redis::get($key);

                    if (isset($models[$type]) && $val > 0) {
                        $models[$type]::where('id', $id)->increment($metric, $val);
                        
                        Redis::del($key);
                    }
                }
            }
        }

        $this->info('Analytics successfully synced to Database!');
        Log::info('Analytics background sync completed.');
    }
}