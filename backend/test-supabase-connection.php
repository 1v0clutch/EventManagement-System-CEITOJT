<?php
/**
 * Quick Supabase S3 connection test
 * Run via: php test-supabase-connection.php
 * Or hit via browser if accessible
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Supabase S3 Connection Test ===\n\n";

echo "ENV Variables:\n";
echo "  SUPABASE_S3_ENDPOINT:      " . (env('SUPABASE_S3_ENDPOINT') ?: '❌ NOT SET') . "\n";
echo "  SUPABASE_S3_REGION:        " . (env('SUPABASE_S3_REGION') ?: '❌ NOT SET') . "\n";
echo "  SUPABASE_S3_BUCKET:        " . (env('SUPABASE_S3_BUCKET') ?: '❌ NOT SET') . "\n";
echo "  SUPABASE_S3_ACCESS_KEY_ID: " . (env('SUPABASE_S3_ACCESS_KEY_ID') ? '✅ SET (' . substr(env('SUPABASE_S3_ACCESS_KEY_ID'), 0, 8) . '...)' : '❌ NOT SET') . "\n";
echo "  SUPABASE_S3_SECRET_ACCESS_KEY: " . (env('SUPABASE_S3_SECRET_ACCESS_KEY') ? '✅ SET' : '❌ NOT SET') . "\n";
echo "  SUPABASE_PUBLIC_URL:       " . (env('SUPABASE_PUBLIC_URL') ?: '❌ NOT SET') . "\n\n";

try {
    $disk = \Illuminate\Support\Facades\Storage::disk('supabase');

    // Try writing a small test file
    $testContent = 'supabase-test-' . time();
    $testPath = 'test/connection-test.txt';

    echo "Attempting to upload test file to: {$testPath}\n";
    $result = $disk->put($testPath, $testContent, 'public');

    if ($result) {
        echo "✅ Upload SUCCESS!\n";

        $publicUrl = rtrim(env('SUPABASE_PUBLIC_URL'), '/') . '/' . env('SUPABASE_S3_BUCKET') . '/' . $testPath;
        echo "✅ Public URL would be: {$publicUrl}\n";

        // Clean up
        $disk->delete($testPath);
        echo "✅ Cleanup done.\n";
    } else {
        echo "❌ Upload returned false (no exception thrown)\n";
    }
} catch (\Exception $e) {
    echo "❌ UPLOAD FAILED: " . $e->getMessage() . "\n";
    echo "   Class: " . get_class($e) . "\n";
}
