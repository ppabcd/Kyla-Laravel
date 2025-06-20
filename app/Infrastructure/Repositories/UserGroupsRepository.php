<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\UserGroups;

class UserGroupsRepository
{
    public function findByUserId($userId)
    {
        return UserGroups::where('id_user', $userId)->first();
    }

    public function create(array $data)
    {
        return UserGroups::create($data);
    }

    public function update(UserGroups $entity, array $data)
    {
        $entity->update($data);
        return $entity;
    }

    public function delete(UserGroups $entity)
    {
        return $entity->delete();
    }
} 
