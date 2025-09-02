<?php

use App\Application\Services\MatchingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

it('can enable random matching via config', function () {
    Config::set('telegram.matching.random_matching', true);

    expect(config('telegram.matching.random_matching'))->toBeTrue();
});

it('defaults random matching to false', function () {
    expect(config('telegram.matching.random_matching'))->toBeFalse();
});

it('matches users with same gender when random matching is enabled', function () {
    Config::set('telegram.matching.random_matching', true);

    // Create two male users who normally wouldn't match
    $user1 = User::factory()->create([
        'gender' => 'male',
        'interest' => 'female',
        'age' => 25,
        'is_banned' => false,
        'is_searching' => true,
    ]);

    $user2 = User::factory()->create([
        'gender' => 'male',  // Same gender
        'interest' => 'female',
        'age' => 27,
        'is_banned' => false,
        'is_searching' => true,
    ]);

    $matchingService = app(MatchingService::class);
    $match = $matchingService->findMatch($user1);

    expect($match)->not->toBeNull();
    expect($match->id)->toBe($user2->id);
});

it('does not match users with same gender when random matching is disabled', function () {
    Config::set('telegram.matching.random_matching', false);

    // Create two male users who shouldn't match in normal mode
    $user1 = User::factory()->create([
        'gender' => 'male',
        'interest' => 'female',
        'age' => 25,
        'is_banned' => false,
        'is_searching' => true,
    ]);

    $user2 = User::factory()->create([
        'gender' => 'male',  // Same gender
        'interest' => 'female',
        'age' => 27,
        'is_banned' => false,
        'is_searching' => true,
    ]);

    $matchingService = app(MatchingService::class);
    $match = $matchingService->findMatch($user1);

    expect($match)->toBeNull();
});
