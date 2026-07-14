<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    public function upload(UploadedFile $file, Model $owner, string $disk = 'public', string $usage_type = null): Media
    {
        $path = $this->storePath($file, $disk);
        $filename = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $metadata = $this->extractMetadata($file, $mimeType);

        return Media::create([
            'owner_type' => get_class($owner),
            'owner_id' => $owner->id,
            'disk' => $disk,
            'path' => $path,
            'file_name' => $filename,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'metadata' => $metadata,
            'usage_type' => $usage_type,
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function delete(Media $media): bool
    {
        Storage::disk($media->disk)->delete($media->path);
        return $media->delete();
    }

    public function replaceImage(Model $owner, UploadedFile $newFile, string $disk = 'public'): Media
    {
        // Delete old image if exists
        $oldMedia = $owner->media()->where('usage_type', 'image')->first();
        if ($oldMedia) {
            $this->delete($oldMedia);
        }

        return $this->upload($newFile, $owner, $disk, 'image');
    }

    private function storePath(UploadedFile $file, string $disk): string
    {
        $date = now()->format('Y/m/d');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "uploads/{$date}/{$filename}";

        $file->storeAs("uploads/{$date}", $filename, $disk);

        return $path;
    }

    private function extractMetadata(UploadedFile $file, string $mimeType): array
    {
        $metadata = [];

        if (str_starts_with($mimeType, 'image/')) {
            if (function_exists('getimagesize')) {
                $imageInfo = @getimagesize($file->getRealPath());
                if ($imageInfo !== false) {
                    $metadata['width'] = $imageInfo[0];
                    $metadata['height'] = $imageInfo[1];
                }
            }
        }

        return $metadata;
    }
}
