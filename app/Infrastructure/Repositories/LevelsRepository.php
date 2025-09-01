<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Levels;

class LevelsRepository
{
    public function findByUserId($userId)
    {
        return Levels::where('id_user', $userId)->get();
    }

    public function create(array $data)
    {
        return Levels::create($data);
    }

    public function update(Levels $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(Levels $entity)
    {
        return $entity->delete();
    }
}
