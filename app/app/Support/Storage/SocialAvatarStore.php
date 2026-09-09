<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialAvatarStore
{
    public function __construct(
        protected StorageDriverManager $storageDriverManager,
    ) {}

    public function localize(?string $url, ?string $providerKey = null, ?string $externalId = null): ?array
    {
        $url = trim((string) $url);

        if (! $this->shouldDownload($url)) {
            return null;
        }

        $response = Http::timeout(20)->get($url);

        if (! $response->successful()) {
            return null;
        }

        $mimeType = strtolower(trim((string) $response->header('Content-Type', '')));

        if ($mimeType === '' || ! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        $body = $response->body();

        $bodyLength = strlen($body);

        if ($body === '' || $bodyLength < 128 || $bodyLength > (5 * 1024 * 1024)) {
            return null;
        }

        $extension = $this->guessExtension($url, $mimeType);
        $disk = $this->storageDriverManager->activeDisk();
        $safeProvider = Str::slug((string) ($providerKey ?: 'provider'));
        $safeExternal = Str::slug((string) ($externalId ?: 'account'));
        $filename = $safeExternal.'-'.Str::lower(Str::random(8)).'.'.$extension;
        $path = trim("social-accounts/avatars/{$safeProvider}/{$filename}", '/');

        Storage::disk($disk)->put($path, $body);

        return [
            'disk' => $disk,
            'path' => $path,
            'url' => $this->storageDriverManager->publicUrl($disk, $path),
        ];
    }

    protected function shouldDownload(string $url): bool
    {
        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));

        if ($host === '' || ($appHost !== '' && $host === $appHost)) {
            return false;
        }

        return true;
    }

    protected function guessExtension(string $url, string $mimeType): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => 'jpg',
        };
    }
}
