<?php

namespace Modules\AppFiles\Support;

use Illuminate\Support\Facades\Http;
use Modules\AdminSettings\Support\OptionStore;
use RuntimeException;

class OnlineMediaSearchService
{
    public function __construct(
        protected OptionStore $options,
    ) {}

    public function providers(string $type = 'all'): array
    {
        $providers = [];

        if ($this->providerEnabled('unsplash') && $this->providerKey('unsplash') !== '' && $type !== 'video') {
            $providers['unsplash'] = __('Unsplash');
        }

        if ($this->providerEnabled('pexels') && $this->providerKey('pexels') !== '') {
            if ($type !== 'video') {
                $providers['pexels_photo'] = __('Pexels Photo');
            }

            if ($type !== 'image') {
                $providers['pexels_video'] = __('Pexels Video');
            }
        }

        if ($this->providerEnabled('pixabay') && $this->providerKey('pixabay') !== '') {
            if ($type !== 'video') {
                $providers['pixabay_photo'] = __('Pixabay Photo');
            }

            if ($type !== 'image') {
                $providers['pixabay_video'] = __('Pixabay Video');
            }
        }

        return $providers;
    }

    public function search(string $query, string $provider, string $type = 'all'): array
    {
        $query = trim($query);

        if ($query === '') {
            throw new RuntimeException(__('Please enter your keyword.'));
        }

        if (! array_key_exists($provider, $this->providers($type))) {
            throw new RuntimeException(__('No provider enabled.'));
        }

        return match ($provider) {
            'unsplash' => $this->searchUnsplash($query),
            'pexels_photo' => $this->searchPexelsPhoto($query),
            'pexels_video' => $this->searchPexelsVideo($query),
            'pixabay_photo' => $this->searchPixabayPhoto($query),
            'pixabay_video' => $this->searchPixabayVideo($query),
            default => [],
        };
    }

    protected function searchUnsplash(string $query): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Client-ID '.$this->providerKey('unsplash'),
        ])->timeout(15)->get('https://api.unsplash.com/search/photos', [
            'query' => $query,
            'per_page' => 24,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect((array) $response->json('results', []))
            ->map(fn ($item) => [
                'id' => (string) data_get($item, 'id'),
                'thumbnail' => (string) data_get($item, 'urls.thumb', ''),
                'full' => (string) data_get($item, 'urls.full', ''),
                'source' => 'unsplash',
                'provider' => 'unsplash',
                'mediaType' => 'image',
                'link' => (string) data_get($item, 'links.html', ''),
                'author' => (string) data_get($item, 'user.name', ''),
            ])
            ->filter(fn ($item) => $item['thumbnail'] !== '' && $item['full'] !== '')
            ->values()
            ->all();
    }

    protected function searchPexelsPhoto(string $query): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->providerKey('pexels'),
        ])->retry(2, 200)->timeout(15)->get('https://api.pexels.com/v1/search', [
            'query' => $query,
            'per_page' => 24,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect((array) $response->json('photos', []))
            ->map(fn ($item) => [
                'id' => (string) data_get($item, 'id'),
                'thumbnail' => (string) data_get($item, 'src.medium', ''),
                'full' => (string) data_get($item, 'src.original', ''),
                'source' => 'pexels',
                'provider' => 'pexels_photo',
                'mediaType' => 'image',
                'link' => (string) data_get($item, 'url', ''),
                'author' => (string) data_get($item, 'photographer', ''),
            ])
            ->filter(fn ($item) => $item['thumbnail'] !== '' && $item['full'] !== '')
            ->values()
            ->all();
    }

    protected function searchPexelsVideo(string $query): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->providerKey('pexels'),
        ])->retry(2, 200)->timeout(15)->get('https://api.pexels.com/videos/search', [
            'query' => $query,
            'per_page' => 24,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect((array) $response->json('videos', []))
            ->map(function ($item) {
                $best = collect((array) data_get($item, 'video_files', []))
                    ->filter(fn ($file) => filled(data_get($file, 'link')))
                    ->sortByDesc(fn ($file) => (int) data_get($file, 'width', 0))
                    ->first();

                return [
                    'id' => (string) data_get($item, 'id'),
                    'thumbnail' => (string) data_get($item, 'image', ''),
                    'full' => (string) data_get($best, 'link', ''),
                    'source' => 'pexels',
                    'provider' => 'pexels_video',
                    'mediaType' => 'video',
                    'link' => (string) data_get($item, 'url', ''),
                    'author' => (string) data_get($item, 'user.name', ''),
                ];
            })
            ->filter(fn ($item) => $item['thumbnail'] !== '' && $item['full'] !== '')
            ->values()
            ->all();
    }

    protected function searchPixabayPhoto(string $query): array
    {
        $response = Http::timeout(15)->get('https://pixabay.com/api/', [
            'key' => $this->providerKey('pixabay'),
            'q' => $query,
            'image_type' => 'photo',
            'per_page' => 24,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect((array) $response->json('hits', []))
            ->map(fn ($item) => [
                'id' => (string) data_get($item, 'id'),
                'thumbnail' => (string) data_get($item, 'previewURL', ''),
                'full' => (string) data_get($item, 'largeImageURL', ''),
                'source' => 'pixabay',
                'provider' => 'pixabay_photo',
                'mediaType' => 'image',
                'link' => (string) data_get($item, 'pageURL', ''),
                'author' => (string) data_get($item, 'user', ''),
            ])
            ->filter(fn ($item) => $item['thumbnail'] !== '' && $item['full'] !== '')
            ->values()
            ->all();
    }

    protected function searchPixabayVideo(string $query): array
    {
        $response = Http::timeout(15)->get('https://pixabay.com/api/videos/', [
            'key' => $this->providerKey('pixabay'),
            'q' => $query,
            'per_page' => 24,
            'page' => 1,
        ]);

        if ($response->failed()) {
            return [];
        }

        return collect((array) $response->json('hits', []))
            ->map(function ($item) {
                $best = collect((array) data_get($item, 'videos', []))
                    ->sortByDesc(fn ($video) => (int) data_get($video, 'width', 0))
                    ->first();

                return [
                    'id' => (string) data_get($item, 'id'),
                    'thumbnail' => (string) data_get($best, 'thumbnail', ''),
                    'full' => (string) data_get($best, 'url', ''),
                    'source' => 'pixabay',
                    'provider' => 'pixabay_video',
                    'mediaType' => 'video',
                    'link' => (string) data_get($item, 'pageURL', ''),
                    'author' => (string) data_get($item, 'user', ''),
                ];
            })
            ->filter(fn ($item) => $item['thumbnail'] !== '' && $item['full'] !== '')
            ->values()
            ->all();
    }

    protected function providerEnabled(string $provider): bool
    {
        return match ($provider) {
            'unsplash' => (string) $this->options->get('file_unsplash_status', '0') === '1',
            'pexels' => (string) $this->options->get('file_pexels_status', '0') === '1',
            'pixabay' => (string) $this->options->get('file_pixabay_status', '0') === '1',
            default => false,
        };
    }

    protected function providerKey(string $provider): string
    {
        return trim(match ($provider) {
            'unsplash' => (string) $this->options->get('file_unsplash_access_key', ''),
            'pexels' => (string) $this->options->get('file_pexels_api_key', ''),
            'pixabay' => (string) $this->options->get('file_pixabay_api_key', ''),
            default => '',
        });
    }
}
