<?php

use App\Infrastructure\Repositories\PairRepository;
use Illuminate\Database\Query\Expression;

it('uses SQL boolean false when ending a pair', function () {
    $pair = Mockery::mock(App\Domain\Entities\Pair::class);

    $pair->shouldReceive('update')
        ->once()
        ->with(Mockery::on(function (array $data) {
            expect($data['status'])->toBe('ended');
            expect($data['active'])->toBeInstanceOf(Expression::class);
            expect($data)->toHaveKeys(['ended_at', 'ended_by_user_id', 'ended_reason']);

            return true;
        }))
        ->andReturn(true);

    $repo = new PairRepository;
    $result = $repo->endPair($pair, 2, 'user_stop');

    expect($result)->toBeTrue();
});

it('normalizes active boolean on update to SQL expression', function () {
    $pair = Mockery::mock(App\Domain\Entities\Pair::class);

    $pair->shouldReceive('update')
        ->once()
        ->with(Mockery::on(function (array $data) {
            expect($data['active'])->toBeInstanceOf(Expression::class);

            return true;
        }))
        ->andReturn(true);

    $repo = new PairRepository;
    $result = $repo->update($pair, ['active' => true]);

    expect($result)->toBeTrue();
});
