<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\MatchPartner;

class MatchPartnerRepository
{
    public function findByUserId($userId)
    {
        return MatchPartner::where('user_id', $userId)->first();
    }

    public function create(array $data)
    {
        return MatchPartner::create($data);
    }

    public function update(MatchPartner $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(MatchPartner $entity)
    {
        return $entity->delete();
    }
}
