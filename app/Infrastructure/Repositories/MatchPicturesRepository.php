<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\MatchPictures;

class MatchPicturesRepository
{
    public function findByUserId($userId)
    {
        return MatchPictures::where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return MatchPictures::create($data);
    }

    public function update(MatchPictures $entity, array $data)
    {
        $entity->update($data);
        return $entity;
    }

    public function delete(MatchPictures $entity)
    {
        return $entity->delete();
    }
} 
