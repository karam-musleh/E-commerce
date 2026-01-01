<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageHelper
{

    // Upload a single image
    public static function uploadImage($model, UploadedFile $file, string $folder, string $type = 'main', string $disk = 'public')
    {
        $path = $file->store($folder, $disk);

        return $model->images()->create([
            'path' => $path,
            'type' => $type,
        ]);
    }

    // Upload multiple images as a gallery
    public static function uploadGallery($model, array $files, string $folder, string $disk = 'custom')
    {
        foreach ($files as $file) {
            self::uploadImage($model, $file, $folder, 'gallery', $disk);
        }
    }

    public static function updateGallery($model, array $newFiles = [], array $deleteIds = [], string $folder = 'products/gallery', string $disk = 'custom')
    {
        // حذف الصور المطلوبة
        foreach ($deleteIds as $id) {
            self::deleteImageById($model, $id);
        }

        // إضافة الصور الجديدة
        self::uploadGallery($model, $newFiles, $folder, $disk);
    }


    // delete single image
    public static function deleteImageById($model, int $imageId)
    {
        $image = $model->images()->where('id', $imageId)->first();
        if ($image) {
            self::deleteImage($image);
        }
    }
    public static function deleteImage($image)
    {
        if ($image && Storage::disk($image->disk)->exists($image->path)) {
            Storage::disk($image->disk)->delete($image->path);
        }

        $image->delete();
    }

    // delete multiple images (gallery)
    public static function deleteGallery($model, string $type = 'gallery')
    {
        $images = $model->images()->where('type', $type)->get();

        foreach ($images as $image) {
            self::deleteImage($image);
        }
    }

    // delete all images associated with the model
    public static function deleteAll($model)
    {
        foreach ($model->images as $image) {
            self::deleteImage($image);
        }
    }

    // update a single image (delete old + upload new)
    public static function updateImage($model, UploadedFile $file, string $folder, string $type = 'main', string $disk = 'custom')
    {
        $oldImage = $model->images()->where('type', $type)->first();

        if ($oldImage) {
            self::deleteImage($oldImage);
        }

        return self::uploadImage($model, $file, $folder, $type, $disk);
    }
}
