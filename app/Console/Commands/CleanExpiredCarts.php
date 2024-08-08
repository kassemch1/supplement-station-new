<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanExpiredCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired carts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting cleanup of expired carts');

        DB::table('carts')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info('Expired carts cleaned up.');
        Log::info('Cleanup completed');

    }
}
