<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class MarkDevicesOffline extends Command
{
    protected $signature = 'devices:mark-offline';

    protected $description = 'Mark devices as offline if they have not been seen for over 60 seconds';

    public function handle(): int
    {
        $count = Device::where('status', 'online')
            ->where(function ($query) {
                $query->where('last_seen_at', '<', now()->subSeconds(60))
                      ->orWhereNull('last_seen_at');
            })
            ->update(['status' => 'offline']);

        if ($count > 0) {
            $this->info("Marked {$count} device(s) as offline.");
        }

        return self::SUCCESS;
    }
}
