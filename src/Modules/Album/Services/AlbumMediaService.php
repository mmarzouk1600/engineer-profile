<?php

namespace Modules\Album\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Modules\Album\Entities\AlbumImage;

class AlbumMediaService
{
    public function uploadImages(Album $album, array $files): array
    {
        $uploaded = [];
        $maxSort = (int) $album->images()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->validateImage($file);

            $disk = config('album.image_disk', 'public');
            $path = config('album.image_path', 'albums/images');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($path, $filename, $disk);

            $thumbnailPath = $this->generateThumbnail($file, $path, $disk);

            $image = $album->images()->create([
                'path' => $storedPath,
                'thumbnail_path' => $thumbnailPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => ++$maxSort,
            ]);

            $uploaded[] = $image;

            if (! $album->cover_image_id) {
                $album->update(['cover_image_id' => $image->id]);
            }
        }

        return $uploaded;
    }

    public function uploadFiles(Album $album, array $files): array
    {
        $uploaded = [];
        $maxSort = (int) $album->files()->max('sort_order');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->validateFile($file);

            $disk = config('album.file_disk', 'local');
            $path = config('album.file_path', 'albums/files');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($path, $filename, $disk);

            $uploaded[] = $album->files()->create([
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => ++$maxSort,
            ]);
        }

        return $uploaded;
    }

    public function deleteImage(Album $album, AlbumImage $image): void
    {
        if ($image->album_id !== $album->id) {
            abort(404);
        }

        $disk = config('album.image_disk', 'public');
        Storage::disk($disk)->delete([$image->path, $image->thumbnail_path]);

        if ($album->cover_image_id === $image->id) {
            $nextCover = $album->images()->where('id', '!=', $image->id)->orderBy('sort_order')->first();
            $album->update(['cover_image_id' => $nextCover?->id]);
        }

        $image->delete();
    }

    public function deleteFile(Album $album, AlbumFile $file): void
    {
        if ($file->album_id !== $album->id) {
            abort(404);
        }

        Storage::disk(config('album.file_disk', 'local'))->delete($file->path);
        $file->delete();
    }

    public function reorderImages(Album $album, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $album->images()->whereKey($id)->update(['sort_order' => $index + 1]);
        }
    }

    public function reorderFiles(Album $album, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            $album->files()->whereKey($id)->update(['sort_order' => $index + 1]);
        }
    }

    private function validateImage(UploadedFile $file): void
    {
        $maxKb = config('album.max_image_size_kb', 10240);
        if ($file->getSize() > $maxKb * 1024) {
            abort(422, 'Image exceeds maximum allowed size.');
        }

        if (! str_starts_with($file->getMimeType() ?? '', 'image/')) {
            abort(422, 'Invalid image file type.');
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        $maxKb = config('album.max_file_size_kb', 102400);
        if ($file->getSize() > $maxKb * 1024) {
            abort(422, 'File exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = config('album.allowed_file_extensions', []);

        if (! in_array($extension, $allowed, true)) {
            abort(422, 'File type not allowed.');
        }
    }

    private function generateThumbnail(UploadedFile $file, string $path, string $disk): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $width = config('album.thumbnail_width', 600);
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }

        $source = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'png' => @imagecreatefrompng($file->getRealPath()),
            'webp' => @imagecreatefromwebp($file->getRealPath()),
            'gif' => @imagecreatefromgif($file->getRealPath()),
            default => null,
        };

        if (! $source) {
            return null;
        }

        $origW = imagesx($source);
        $origH = imagesy($source);

        if ($origW <= $width) {
            imagedestroy($source);

            return null;
        }

        $newH = (int) round($origH * ($width / $origW));
        $thumb = imagecreatetruecolor($width, $newH);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width, $newH, $origW, $origH);

        $thumbName = Str::uuid() . '_thumb.' . $extension;
        $thumbPath = $path . '/' . $thumbName;
        $fullPath = Storage::disk($disk)->path($thumbPath);

        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($thumb, $fullPath, 85),
            'png' => imagepng($thumb, $fullPath, 6),
            'webp' => imagewebp($thumb, $fullPath, 85),
            'gif' => imagegif($thumb, $fullPath),
            default => null,
        };

        imagedestroy($source);
        imagedestroy($thumb);

        return $thumbPath;
    }
}
