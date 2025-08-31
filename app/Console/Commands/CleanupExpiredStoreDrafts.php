<?php

namespace App\Console\Commands;

use App\Models\StoreDraft;
use Illuminate\Console\Command;

class CleanupExpiredStoreDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store-drafts:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired store drafts to free up database space';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired store drafts...');

        // Get count of expired drafts
        $expiredCount = StoreDraft::expired()->count();

        if ($expiredCount === 0) {
            $this->info('No expired drafts found.');
            return 0;
        }

        $this->info("Found {$expiredCount} expired draft(s).");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: The following drafts would be deleted:');
            
            StoreDraft::expired()
                ->with('user:id,name,email')
                ->get()
                ->each(function ($draft) {
                    $this->line("- Draft ID: {$draft->id}, User: {$draft->user->name} ({$draft->user->email}), Expired: {$draft->expires_at->diffForHumans()}");
                });

            return 0;
        }

        if ($this->confirm("Are you sure you want to delete {$expiredCount} expired draft(s)?")) {
            $deletedCount = StoreDraft::cleanupExpired();
            $this->info("Successfully deleted {$deletedCount} expired draft(s).");
        } else {
            $this->info('Cleanup cancelled.');
        }

        return 0;
    }
}
