<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\MatchReport;

class MatchReportRepository
{
    public function findByReporterId($reporterId)
    {
        return MatchReport::where('reporter_id', $reporterId)->get();
    }

    public function create(array $data)
    {
        return MatchReport::create($data);
    }

    public function update(MatchReport $entity, array $data)
    {
        $entity->update($data);

        return $entity;
    }

    public function delete(MatchReport $entity)
    {
        return $entity->delete();
    }
}
