<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait StoresMedia
{
    private function storeUpload(UploadedFile $file, int $songId, string $kind): array
    {
        $directory = rtrim(config('utaone.storage_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR."songs/{$songId}/source";
        if (! is_dir($directory) && ! mkdir($directory, 0770, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Unable to create media directory: {$directory}");
        }
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $path = $directory.DIRECTORY_SEPARATOR.$kind.'-'.Str::uuid().'.'.$extension;
        $file->move($directory, basename($path));

        return [$path, hash_file('sha256', $path)];
    }
}
