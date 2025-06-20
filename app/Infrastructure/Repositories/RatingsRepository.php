<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Ratings;

class RatingsRepository
{
    public function findByUserId($userId)
    {
        return Ratings::where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return Ratings::create($data);
    }

    public function update(Ratings $entity, array $data)
    {
        $entity->update($data);
        return $entity;
    }

    public function delete(Ratings $entity)
    {
        return $entity->delete();
    }
} 
