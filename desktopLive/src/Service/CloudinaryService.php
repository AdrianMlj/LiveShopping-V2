<?php
namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct(string $cloudinaryUrl)
    {
        // Initialisation avec CLOUDINARY_URL
        $this->cloudinary = new Cloudinary($cloudinaryUrl);
    }

    public function uploadImageResized(UploadedFile $file, string $folder, int $width, int $height): string
    {
        // Utiliser l'API Upload via l'objet Cloudinary
        $result = $this->cloudinary->uploadApi()->upload($file->getPathname(), [
            'folder' => $folder,
            'transformation' => [
                'width' => $width,
                'height' => $height,
                'crop' => 'limit'
            ]
        ]);

        return $result['secure_url'];
    }

    public function deleteImage(string $imageUrl): void
    {
        // Extraire le public_id de l'URL Cloudinary
        preg_match('/\/upload\/(?:v\d+\/)?(.+)\.[^.]+$/', $imageUrl, $matches);
        if (isset($matches[1])) {
            $publicId = $matches[1];
            // Utiliser l'API Upload via l'objet Cloudinary
            $this->cloudinary->uploadApi()->destroy($publicId);
        }
    }
}
