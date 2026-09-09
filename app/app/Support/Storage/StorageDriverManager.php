<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;

class StorageDriverManager
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    public function configureDisks(): void
    {
        Config::set('filesystems.disks.amazon', $this->diskConfig('amazon', [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ]));

        Config::set('filesystems.disks.wasabi', $this->diskConfig('wasabi', [
            'key' => env('WASABI_ACCESS_KEY_ID'),
            'secret' => env('WASABI_SECRET_ACCESS_KEY'),
            'region' => env('WASABI_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('WASABI_BUCKET'),
            'url' => env('WASABI_URL'),
            'endpoint' => env('WASABI_ENDPOINT'),
            'use_path_style_endpoint' => env('WASABI_USE_PATH_STYLE_ENDPOINT', false),
        ]));

        Config::set('filesystems.disks.contabo', $this->diskConfig('contabo', [
            'key' => env('CONTABO_ACCESS_KEY_ID'),
            'secret' => env('CONTABO_SECRET_ACCESS_KEY'),
            'region' => env('CONTABO_DEFAULT_REGION', 'eu-central-1'),
            'bucket' => env('CONTABO_BUCKET'),
            'url' => env('CONTABO_URL'),
            'endpoint' => env('CONTABO_ENDPOINT'),
            'use_path_style_endpoint' => env('CONTABO_USE_PATH_STYLE_ENDPOINT', true),
        ]));
    }

    public function activeDisk(): string
    {
        $disk = trim((string) $this->options->get('appfiles_disk', config('modules.appfiles.disk', 'public')));

        return array_key_exists($disk, $this->diskOptions()) ? $disk : 'public';
    }

    public function diskOptions(): array
    {
        return [
            'public' => __('Local'),
            'amazon' => __('Amazon S3'),
            'wasabi' => __('Wasabi'),
            'contabo' => __('Contabo'),
        ];
    }

    public function publicUrl(string $disk, ?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if ($disk === 'public') {
            $normalizedPath = ltrim($path, '/');
            $basePath = '';

            try {
                if (app()->bound('request')) {
                    $basePath = trim((string) request()->getBaseUrl());
                }
            } catch (\Throwable) {
                $basePath = '';
            }

            if ($basePath === '') {
                $basePath = trim((string) parse_url((string) config('app.url', ''), PHP_URL_PATH));
            }

            $basePath = trim($basePath, '/');

            return '/'.($basePath !== '' ? $basePath.'/' : '').'storage/app/public/'.$normalizedPath;
        }

        try {
            return (string) Storage::disk($disk)->url($path);
        } catch (\Throwable) {
            return null;
        }
    }

    public function diskHealth(?string $disk = null): array
    {
        $disk = trim((string) ($disk ?: $this->activeDisk()));

        if ($disk === '' || $disk === 'public' || $disk === 'local') {
            return [
                'disk' => $disk !== '' ? $disk : 'public',
                'provider' => $this->diskOptions()[$disk !== '' ? $disk : 'public'] ?? __('Local'),
                'valid' => true,
                'title' => null,
                'message' => null,
                'details' => [],
            ];
        }

        $config = (array) config("filesystems.disks.{$disk}", []);
        $provider = $this->diskOptions()[$disk] ?? Str::headline($disk);
        $missing = collect($this->requiredFieldsForDisk($disk))
            ->filter(fn (string $field): bool => trim((string) ($config[$field] ?? '')) === '')
            ->values()
            ->all();

        if ($missing !== []) {
            return [
                'disk' => $disk,
                'provider' => $provider,
                'valid' => false,
                'title' => __('Storage configuration is incomplete'),
                'message' => __(':provider is selected for file storage, but these fields are missing: :fields. Update the storage settings before uploading files.', [
                    'provider' => $provider,
                    'fields' => implode(', ', $missing),
                ]),
                'details' => $missing,
            ];
        }

        $cacheKey = 'storage-disk-health:'.$disk.':'.md5(json_encode([
            'key' => (string) ($config['key'] ?? ''),
            'region' => (string) ($config['region'] ?? ''),
            'bucket' => (string) ($config['bucket'] ?? ''),
            'endpoint' => (string) ($config['endpoint'] ?? ''),
        ]));

        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($disk, $provider) {
            try {
                Storage::disk($disk)->files('', false);

                return [
                    'disk' => $disk,
                    'provider' => $provider,
                    'valid' => true,
                    'title' => null,
                    'message' => null,
                    'details' => [],
                ];
            } catch (\Throwable $exception) {
                return [
                    'disk' => $disk,
                    'provider' => $provider,
                    'valid' => false,
                    'title' => __('Storage connection failed'),
                    'message' => __('Could not connect to :provider with the current storage settings. Check bucket, region, endpoint, and access keys before uploading files.', [
                        'provider' => $provider,
                    ]),
                    'details' => [$exception->getMessage()],
                ];
            }
        });
    }

    public function ensureDiskReadyForWrites(?string $disk = null): void
    {
        $health = $this->diskHealth($disk);

        if ($health['valid'] ?? false) {
            return;
        }

        throw new RuntimeException((string) ($health['message'] ?? __('The configured storage disk is not ready for file uploads.')));
    }

    public function resolveLocalPath(string $disk, string $path, ?string $suggestedName = null): array
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException(__('The requested file could not be found in storage.'));
        }

        if (in_array($disk, ['local', 'public'], true)) {
            return [
                'path' => Storage::disk($disk)->path($path),
                'temporary' => false,
            ];
        }

        $stream = Storage::disk($disk)->readStream($path);

        if (! is_resource($stream)) {
            throw new \RuntimeException(__('Could not read the stored file stream.'));
        }

        $extension = strtolower((string) pathinfo((string) $suggestedName, PATHINFO_EXTENSION));
        $filename = Str::uuid()->toString().($extension !== '' ? '.'.$extension : '');
        $directory = storage_path('app/temp-storage/'.now()->format('Y/m/d'));
        File::ensureDirectoryExists($directory);
        $localPath = $directory.DIRECTORY_SEPARATOR.$filename;
        $target = fopen($localPath, 'wb');

        if (! is_resource($target)) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            throw new \RuntimeException(__('Could not create a temporary local file.'));
        }

        stream_copy_to_stream($stream, $target);
        fclose($stream);
        fclose($target);

        return [
            'path' => $localPath,
            'temporary' => true,
        ];
    }

    public function deleteIfPresent(?string $disk, ?string $path): void
    {
        $disk = trim((string) $disk);
        $path = trim((string) $path);

        if ($disk === '' || $path === '') {
            return;
        }

        try {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable) {
        }
    }

    protected function diskConfig(string $provider, array $defaults): array
    {
        return [
            'driver' => 's3',
            'key' => (string) $this->options->get("appfiles_{$provider}_access_key_id", $defaults['key'] ?? ''),
            'secret' => (string) $this->options->get("appfiles_{$provider}_secret_access_key", $defaults['secret'] ?? ''),
            'region' => (string) $this->options->get("appfiles_{$provider}_default_region", $defaults['region'] ?? ''),
            'bucket' => (string) $this->options->get("appfiles_{$provider}_bucket", $defaults['bucket'] ?? ''),
            'url' => (string) $this->options->get("appfiles_{$provider}_url", $defaults['url'] ?? ''),
            'endpoint' => (string) $this->options->get("appfiles_{$provider}_endpoint", $defaults['endpoint'] ?? ''),
            'use_path_style_endpoint' => $this->booleanOption(
                "appfiles_{$provider}_use_path_style_endpoint",
                (bool) ($defaults['use_path_style_endpoint'] ?? false)
            ),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ];
    }

    protected function booleanOption(string $key, bool $default = false): bool
    {
        return filter_var($this->options->get($key, $default ? '1' : '0'), FILTER_VALIDATE_BOOL);
    }

    protected function requiredFieldsForDisk(string $disk): array
    {
        return match ($disk) {
            'amazon' => ['key', 'secret', 'region', 'bucket'],
            'wasabi', 'contabo' => ['key', 'secret', 'region', 'bucket', 'endpoint'],
            default => ['key', 'secret', 'region', 'bucket'],
        };
    }
}
