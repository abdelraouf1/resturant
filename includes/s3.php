<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

function get_s3_client(): S3Client
{
    static $client = null;
    if ($client !== null) return $client;

    $config = [
        'version' => 'latest',
        'region'  => env('AWS_REGION', 'us-east-1'),
    ];

    // On EC2, prefer an IAM Instance Role (no keys needed at all — recommended).
    // Only pass explicit keys if they're set in .env (useful for local dev).
    $key = env('AWS_ACCESS_KEY_ID');
    $secret = env('AWS_SECRET_ACCESS_KEY');
    if ($key && $secret) {
        $config['credentials'] = [
            'key'    => $key,
            'secret' => $secret,
        ];
    }

    $client = new S3Client($config);
    return $client;
}

/**
 * Uploads a local file to S3 under menu/ and returns [key, url] or null on failure.
 */
function s3_upload_menu_image(string $localTmpPath, string $originalName): ?array
{
    $bucket = env('S3_BUCKET');
    if (!$bucket) {
        error_log('S3_BUCKET not configured');
        return null;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowed, true)) {
        return null;
    }

    $key = 'menu/' . bin2hex(random_bytes(8)) . '.' . $ext;

    try {
        $client = get_s3_client();
        $client->putObject([
            'Bucket'      => $bucket,
            'Key'         => $key,
            'SourceFile'  => $localTmpPath,
            'ContentType' => match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'webp'        => 'image/webp',
                default       => 'application/octet-stream',
            },
            // Bucket should have public-read via bucket policy for a menu image gallery
            // (see README). Avoids relying on deprecated per-object ACLs.
        ]);
    } catch (AwsException $e) {
        error_log('S3 upload failed: ' . $e->getMessage());
        return null;
    }

    $url = s3_public_url($key);
    return ['key' => $key, 'url' => $url];
}

function s3_delete_object(?string $key): void
{
    if (!$key) return;
    $bucket = env('S3_BUCKET');
    if (!$bucket) return;

    try {
        get_s3_client()->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $key,
        ]);
    } catch (AwsException $e) {
        error_log('S3 delete failed: ' . $e->getMessage());
    }
}

function s3_public_url(string $key): string
{
    $cf = env('CLOUDFRONT_DOMAIN');
    if ($cf) {
        return 'https://' . rtrim($cf, '/') . '/' . $key;
    }
    $bucket = env('S3_BUCKET');
    $region = env('AWS_REGION', 'us-east-1');
    return "https://{$bucket}.s3.{$region}.amazonaws.com/{$key}";
}
