<?php

declare(strict_types=1);

namespace App\Actions\Settings\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ManagesProductImages
{
    private function storeUploadedImage(UploadedFile $imageFile): string
    {
        $path = $imageFile->store('products', 'public');

        return '/storage/'.$path;
    }

    private function deleteManagedImage(?string $imageUrl): void
    {
        if (! $this->isManagedImage($imageUrl)) {
            return;
        }

        if (! is_string($imageUrl)) {
            return;
        }

        $path = Str::after($imageUrl, '/storage/');

        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    private function isManagedImage(?string $imageUrl): bool
    {
        return is_string($imageUrl) && Str::startsWith($imageUrl, '/storage/products/');
    }
}
