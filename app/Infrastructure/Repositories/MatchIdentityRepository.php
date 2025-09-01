<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\MatchIdentity;

class MatchIdentityRepository
{
    public function findByUserId($userId)
    {
        return MatchIdentity::where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return MatchIdentity::create($data);
    }

    public function update(MatchIdentity $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(MatchIdentity $entity)
    {
        return $entity->delete();
    }
}
