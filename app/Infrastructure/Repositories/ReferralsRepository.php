<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Referrals;

class ReferralsRepository
{
    public function findByReferrerId($referrerId)
    {
        return Referrals::where('referrer_id', $referrerId)->get();
    }

    public function create(array $data)
    {
        return Referrals::create($data);
    }

    public function update(Referrals $entity, array $data)
    {
        $entity->update($data);
        return $entity;
    }

    public function delete(Referrals $entity)
    {
        return $entity->delete();
    }
} 
