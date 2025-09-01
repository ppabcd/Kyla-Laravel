<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Reviews;

class ReviewsRepository
{
    public function findByUserId($userId)
    {
        return Reviews::where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return Reviews::create($data);
    }

    public function update(Reviews $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(Reviews $entity)
    {
        return $entity->delete();
    }
}
