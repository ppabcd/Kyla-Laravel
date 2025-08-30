<?php

use App\Infrastructure\Repositories\PairPendingRepository;
use Illuminate\Database\Eloquent\Builder;

it('orders FIFO with id tie-breaker when finding available match', function () {
    // Mock the Eloquent Builder chain
    $builder = Mockery::mock(Builder::class);

    $builder->shouldReceive('where')->andReturnSelf()->byDefault();
    $builder->shouldReceive('orderBy')->with('created_at', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('orderBy')->with('id', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('first')->once()->andReturn((object) ['id' => 1]);

    // Mock static query() on the model
    $model = Mockery::mock('alias:App\\Models\\PairPending');
    $model->shouldReceive('query')->andReturn($builder);

    $repo = new PairPendingRepository;
    $match = $repo->findAvailableMatch('male', 'female');

    expect($match)->toBeObject();
});

it('orders pending list FIFO with id tie-breaker', function () {
    $builder = Mockery::mock(Builder::class);

    $builder->shouldReceive('orderBy')->with('created_at', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('orderBy')->with('id', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('get')->once()->andReturn(collect());

    $model = Mockery::mock('alias:App\\Models\\PairPending');
    $model->shouldReceive('orderBy')->andReturnUsing(function (...$args) use ($builder) {
        // When called statically, proxy to our builder expectations
        return $builder->orderBy(...$args);
    });

    $repo = new PairPendingRepository;
    $list = $repo->findPendingPairs();

    expect($list)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});

it('orders next pending candidate FIFO with id tie-breaker', function () {
    $builder = Mockery::mock(Builder::class);

    $builder->shouldReceive('where')->with('user_id', '!=', 123)->once()->andReturnSelf();
    $builder->shouldReceive('orderBy')->with('created_at', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('orderBy')->with('id', 'ASC')->once()->andReturnSelf();
    $builder->shouldReceive('first')->once()->andReturnNull();

    $model = Mockery::mock('alias:App\\Models\\PairPending');
    $model->shouldReceive('where')->andReturnUsing(function (...$args) use ($builder) {
        return $builder->where(...$args);
    });

    $repo = new PairPendingRepository;
    $match = $repo->findNextPendingPair(123);

    expect($match)->toBeNull();
});
