<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\MatchPartnerHistory;

class MatchPartnerHistoryRepository
{
    public function findByUserId($userId)
    {
        return MatchPartnerHistory::where('user_id', $userId)->get();
    }

    public function create(array $data)
    {
        return MatchPartnerHistory::create($data);
    }

    public function update(MatchPartnerHistory $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(MatchPartnerHistory $entity)
    {
        return $entity->delete();
    }
}
