<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait StoresMedia
{
    private function storeUpload(UploadedFile $file, int $songId, string $kind): array
    {
        $storageRoot = rtrim(config('utaone.storage_path'), DIRECTORY_SEPARATOR);
        $paths = [
            $storageRoot,
            $storageRoot.DIRECTORY_SEPARATOR.'songs',
            $storageRoot.DIRECTORY_SEPARATOR.'songs'.DIRECTORY_SEPARATOR.$songId,
            $storageRoot.DIRECTORY_SEPARATOR.'songs'.DIRECTORY_SEPARATOR.$songId.DIRECTORY_SEPARATOR.'source',
        ];
        foreach ($paths as $path) {
            if (! is_dir($path) && ! @mkdir($path, 0770) && ! is_dir($path)) {
                throw new \RuntimeException("Unable to create media directory: {$path}");
            }
            @chmod($path, 0770);
        }
        $directory = end($paths);
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $path = $directory.DIRECTORY_SEPARATOR.$kind.'-'.Str::uuid().'.'.$extension;
        $file->move($directory, basename($path));

        return [$path, hash_file('sha256', $path)];
    }
}
