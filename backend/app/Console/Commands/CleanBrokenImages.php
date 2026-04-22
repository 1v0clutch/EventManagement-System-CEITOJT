<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanBrokenImages extends Command
{
    protected $signature = 'images:clean-broken';
    protected $description = 'Remove broken event image records and profile pictures that are not valid HTTP URLs';

    public function handle()
    {
        // 1. Delete event_images that point to old Render/local storage (not a full http URL)
        $deletedImages = DB::table('event_images')
            ->where(function ($q) {
                $q->where('cloudinary_url', 'not like', 'http%')
                  ->orWhereNull('cloudinary_url')
                  ->orWhere('cloudinary_url', 'like', '%onrender.com%')
                  ->orWhere('cloudinary_url', 'like', '%localhost%');
            })
            ->delete();

        $this->info("Deleted {$deletedImages} broken event image record(s).");

        // 2. Clear profile pictures that are not valid HTTP URLs
        $clearedProfiles = DB::table('users')
            ->whereNotNull('profile_picture')
            ->where(function ($q) {
                $q->where('profile_picture', 'not like', 'http%')
                  ->orWhere('profile_picture', 'like', '%onrender.com%')
                  ->orWhere('profile_picture', 'like', '%localhost%');
            })
            ->update([
                'profile_picture' => null,
                'profile_picture_public_id' => null,
            ]);

        $this->info("Cleared {$clearedProfiles} broken profile picture(s).");

        $this->info('Done! All broken image records have been cleaned up.');

        return Command::SUCCESS;
    }
}
