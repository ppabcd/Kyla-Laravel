<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\StartToken;

class StartTokenRepository
{
    public function findByToken($token)
    {
        return StartToken::where('token', $token)->first();
    }

    public function create(array $data)
    {
        return StartToken::create($data);
    }

    public function update(StartToken $entity, array $data)
    {
        $entity->update($data);
        return $entity;
    }

    public function delete(StartToken $entity)
    {
        return $entity->delete();
    }
} 
