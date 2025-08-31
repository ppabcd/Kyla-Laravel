<?php

use App\Infrastructure\Repositories\PairPendingRepository;
use App\Models\PairPending;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->pairPendingRepo = app(PairPendingRepository::class);
});

test('detects queue overcrowding correctly', function () {
    // Create 6 pending pairs (more than threshold of 5)
    PairPending::factory()->count(6)->create();

    expect($this->pairPendingRepo->isQueueOvercrowded())->toBeTrue();
    expect($this->pairPendingRepo->isQueueOvercrowded(10))->toBeFalse();
});

test('detects gender balance correctly', function () {
    // Create balanced queue (3 males, 3 females)
    PairPending::factory()->count(3)->create(['gender' => 1]); // males
    PairPending::factory()->count(3)->create(['gender' => 2]); // females

    $balance = $this->pairPendingRepo->getGenderBalance();

    expect($balance['male_count'])->toBe(3);
    expect($balance['female_count'])->toBe(3);
    expect($balance['is_balanced'])->toBeTrue();
});

test('detects gender imbalance correctly', function () {
    // Create imbalanced queue (1 male, 6 females)
    PairPending::factory()->count(1)->create(['gender' => 1]); // males
    PairPending::factory()->count(6)->create(['gender' => 2]); // females

    $balance = $this->pairPendingRepo->getGenderBalance();

    expect($balance['male_count'])->toBe(1);
    expect($balance['female_count'])->toBe(6);
    expect($balance['is_balanced'])->toBeFalse();
});

test('identifies underrepresented gender correctly', function () {
    // Create imbalanced queue (1 male, 4 females)
    PairPending::factory()->count(1)->create(['gender' => 1]); // males
    PairPending::factory()->count(4)->create(['gender' => 2]); // females

    $underrepresented = $this->pairPendingRepo->getUnderrepresentedGender();

    expect($underrepresented)->toBe(1); // male is underrepresented
});

test('returns null when gender is balanced', function () {
    // Create balanced queue
    PairPending::factory()->count(3)->create(['gender' => 1]); // males
    PairPending::factory()->count(3)->create(['gender' => 2]); // females

    $underrepresented = $this->pairPendingRepo->getUnderrepresentedGender();

    expect($underrepresented)->toBeNull();
});

test('handles empty queue correctly', function () {
    expect($this->pairPendingRepo->isQueueOvercrowded())->toBeFalse();
    expect($this->pairPendingRepo->isGenderBalanced())->toBeTrue();
    expect($this->pairPendingRepo->getUnderrepresentedGender())->toBeNull();
});
