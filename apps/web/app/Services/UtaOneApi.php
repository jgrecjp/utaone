<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class UtaOneApi
{
    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('utaone.api_url'), '/'))
            ->withToken(config('utaone.admin_api_token'))
            ->acceptJson()
            ->timeout(120);
    }

    public function songs(): array
    {
        return $this->successful($this->client()->get('/v1/admin/songs'))->json();
    }

    public function createSong(array $attributes): array
    {
        return $this->successful($this->client()->post('/v1/admin/songs', $attributes))->json();
    }

    public function uploadAsset(int $songId, string $kind, UploadedFile $file): array
    {
        $response = $this->client()
            ->attach('asset', fopen($file->getRealPath(), 'rb'), $file->getClientOriginalName(), [
                'Content-Type' => $file->getMimeType() ?: 'application/octet-stream',
            ])
            ->post("/v1/admin/songs/{$songId}/assets", ['kind' => $kind]);

        return $this->successful($response)->json();
    }

    public function enqueue(int $songId): array
    {
        return $this->successful($this->client()->post("/v1/admin/songs/{$songId}/jobs"))->json();
    }

    public function job(int $jobId): array
    {
        return $this->successful($this->client()->get("/v1/admin/jobs/{$jobId}"))->json();
    }

    public function timeline(int $songId): array
    {
        return $this->successful($this->client()->get("/v1/admin/songs/{$songId}/timeline"))->json();
    }

    public function updateTimeline(int $songId, array $segments): array
    {
        return $this->successful($this->client()->put("/v1/admin/songs/{$songId}/timeline", [
            'segments' => $segments,
        ]))->json();
    }

    public function publish(int $songId): array
    {
        return $this->successful($this->client()->post("/v1/admin/songs/{$songId}/publish"))->json();
    }

    private function successful(Response $response): Response
    {
        $response->throw();
        return $response;
    }
}
