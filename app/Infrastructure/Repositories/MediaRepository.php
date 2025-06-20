<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Media;

class MediaRepository
{
    public function findByFileUniqueId($fileUniqueId)
    {
        return Media::where('file_unique_id', $fileUniqueId)->first();
    }

    public function create(array $data)
    {
        return Media::create($data);
    }

    public function update(Media $media, array $data)
    {
        $media->update($data);
        return $media;
    }

    public function delete(Media $media)
    {
        return $media->delete();
    }
} 
