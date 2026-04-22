<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        // Add cloudinary_url to event_images for persistent CDN URLs
        Schema::table('event_images', function (Blueprint $table) {
            $table->string('cloudinary_url')->nullable()->after('image_path');
        });

        // Add profile_picture_public_id to users for Cloudinary deletion support
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_picture_public_id')->nullable()->after('profile_picture');
        });
    }

    public function down(): void
    {
        Schema::table('event_images', function (Blueprint $table) {
            $table->dropColumn('cloudinary_url');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_picture_public_id');
        });
    }
};
