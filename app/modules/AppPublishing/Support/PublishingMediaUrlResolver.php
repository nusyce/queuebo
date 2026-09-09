<?php

namespace Modules\AppPublishing\Support;

use App\Support\Storage\StorageDriverManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Modules\AppFiles\Models\AppFile;

class PublishingMediaUrlResolver
{
    public function __construct(
        protected StorageDriverManager $storageDriverManager,
    ) {}

    public function resolveFiles(array $items): Collection
    {
        $ids = collect($items)
            ->map(fn ($item) => (int) Arr::get($item, 'id'))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return AppFile::query()
            ->whereIn('id', $ids->all())
            ->get()
            ->sortBy(fn (AppFile $file) => $ids->search($file->id))
            ->values();
    }

    public function publicUrlForFile(AppFile $file, int $expiresInMinutes = 360): string
    {
        $directUrl = $this->storageDriverManager->publicUrl((string) $file->disk, (string) $file->path);

        if ($directUrl) {
            return $directUrl;
        }

        return URL::temporarySignedRoute(
            'portal.files.publish-preview',
            now()->addMinutes($expiresInMinutes),
            ['file' => $file]
        );
    }

    public function firstAbsoluteUrl(array $mediaItem): ?string
    {
        foreach (['url', 'previewUrl', 'embedUrl'] as $key) {
            $value = trim((string) Arr::get($mediaItem, $key, ''));

            if ($value !== '' && preg_match('#^https?://#i', $value)) {
                return $value;
            }
        }

        return null;
    }
}
