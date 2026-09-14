<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeDeletedUserContactInfo extends Command
{
    protected $signature = 'users:purge-contacts';

    protected $description = 'Clear email and phone numbers for users soft-deleted over 30 days ago';

    public function handle(): void
    {
        $cutoffDate = Carbon::now()->subDays(30);

        // Fetch soft-deleted users older than 30 days who still have contact info
        $count = User::onlyTrashed()
            ->where('deleted_at', '<=', $cutoffDate)
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone');
            })
            ->update([
                'email' => null,
                'phone' => null,
            ]);

        $this->info("Successfully cleared contact info for {$count} user(s).");
    }
}
