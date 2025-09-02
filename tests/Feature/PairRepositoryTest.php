<?php

use App\Infrastructure\Repositories\PairRepository;
use App\Models\Pair;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sets active to false when ending a pair', function () {
    $user = User::factory()->create();
    $partner = User::factory()->create();

    $pair = Pair::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'status' => 'active',
        'active' => true,
    ]);

    $repo = new PairRepository;
    $result = $repo->endPair($pair, $user->id, 'user_stop');

    expect($result)->toBeTrue();

    $pair->refresh();
    expect($pair->status)->toBe('ended');
    expect($pair->active)->toBeFalse();
    expect($pair->ended_by_user_id)->toBe($user->id);
    expect($pair->ended_reason)->toBe('user_stop');
    expect($pair->ended_at)->not->toBeNull();
});

it('normalizes active boolean on update', function () {
    $user = User::factory()->create();
    $partner = User::factory()->create();

    $pair = Pair::factory()->create([
        'user_id' => $user->id,
        'partner_id' => $partner->id,
        'active' => false,
    ]);

    $repo = new PairRepository;
    $result = $repo->update($pair, ['active' => true]);

    expect($result)->toBeTrue();

    $pair->refresh();
    expect($pair->active)->toBeTrue();
});
