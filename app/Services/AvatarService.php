<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class AvatarService
{
    public function store(UploadedFile $file): string
    {
        $path = 'avatars/'.Str::uuid().'.jpg';
        try {
            // v3 auto-orients EXIF by default before the centered crop.
            $image = (new ImageManager(new Driver))->read($file->getRealPath());
            $bytes = (string) $image->cover(256, 256, 'center')->toJpeg(85);
            if (! Storage::disk('public')->put($path, $bytes, ['ContentType' => 'image/jpeg'])) {
                throw new RuntimeException('Avatar write failed.');
            }
        } catch (Throwable $exception) {
            $this->delete($path);
            throw $exception;
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }
        try {
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('Avatar cleanup did not complete.', ['path' => $path]);
            }
        } catch (Throwable $exception) {
            // Cleanup must never turn a committed account update into a failure.
            Log::warning('Avatar cleanup needs retry.', ['path' => $path, 'exception' => $exception->getMessage()]);
        }
    }

    public function url(?string $path): string
    {
        try {
            if ($path && Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return asset('images/avatar-placeholder.svg');
    }
}
