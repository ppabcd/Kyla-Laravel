<?php

use App\Infrastructure\Repositories\PairPendingRepository;
use App\Models\PairPending;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = new PairPendingRepository;
});

describe('Random Gender Matching', function () {
    it('matches users with random preference to users wanting their gender', function () {
        // Create a male user with random preference (interest = null)
        $maleUser = User::factory()->create(['gender' => 'male', 'interest' => null]);

        // Create a female user looking specifically for males
        $femaleUser = User::factory()->create(['gender' => 'female', 'interest' => 'male']);

        // Add female user to pending queue
        PairPending::create([
            'user_id' => $femaleUser->id,
            'gender' => 2, // female
            'interest' => 1, // wants male
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);

        // Male user with random preference should match with female user wanting males
        $match = $this->repository->findAvailableMatch($maleUser->gender, $maleUser->interest);

        expect($match)->not->toBeNull();
        expect($match->user_id)->toBe($femaleUser->id);
    });

    it('matches users with random preference to other random users', function () {
        // Create two users both with random preference
        $user1 = User::factory()->create(['gender' => 'male', 'interest' => null]);
        $user2 = User::factory()->create(['gender' => 'female', 'interest' => null]);

        // Add second user to pending queue with random preference
        PairPending::create([
            'user_id' => $user2->id,
            'gender' => 2, // female
            'interest' => null, // random
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);

        // First user with random preference should match with second user also having random preference
        $match = $this->repository->findAvailableMatch($user1->gender, $user1->interest);

        expect($match)->not->toBeNull();
        expect($match->user_id)->toBe($user2->id);
    });

    it('matches users wanting opposite gender with random users', function () {
        // Create a male user wanting females specifically
        $maleUser = User::factory()->create(['gender' => 'male', 'interest' => 'female']);

        // Create a female user with random preference
        $femaleUser = User::factory()->create(['gender' => 'female', 'interest' => null]);

        // Add female user with random preference to pending queue
        PairPending::create([
            'user_id' => $femaleUser->id,
            'gender' => 2, // female
            'interest' => null, // random
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);

        // Male user wanting females should match with female user having random preference
        $match = $this->repository->findAvailableMatch($maleUser->gender, $maleUser->interest);

        expect($match)->not->toBeNull();
        expect($match->user_id)->toBe($femaleUser->id);
    });

    it('respects gender preferences when not random', function () {
        // Create a male user wanting females specifically
        $maleUser = User::factory()->create(['gender' => 'male', 'interest' => 'female']);

        // Create a male user wanting males (should not match)
        $otherMaleUser = User::factory()->create(['gender' => 'male', 'interest' => 'male']);

        // Add the other male user to pending queue
        PairPending::create([
            'user_id' => $otherMaleUser->id,
            'gender' => 1, // male
            'interest' => 1, // wants male
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);

        // Male user wanting females should NOT match with male user wanting males
        $match = $this->repository->findAvailableMatch($maleUser->gender, $maleUser->interest);

        expect($match)->toBeNull();
    });

    it('finds oldest pending user first', function () {
        // Create three users with random preference
        $user1 = User::factory()->create(['gender' => 'male', 'interest' => null]);
        $user2 = User::factory()->create(['gender' => 'female', 'interest' => null]);
        $user3 = User::factory()->create(['gender' => 'female', 'interest' => null]);

        // Create older pending entry first
        $olderTime = now()->subMinutes(10);
        $olderPending = PairPending::create([
            'user_id' => $user3->id,
            'gender' => 2,
            'interest' => null,
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);
        // Force update the timestamps to make it older
        \DB::table('pair_pendings')
            ->where('id', $olderPending->id)
            ->update([
                'created_at' => $olderTime,
                'updated_at' => $olderTime,
            ]);

        // Create newer pending entry
        $newerPending = PairPending::create([
            'user_id' => $user2->id,
            'gender' => 2,
            'interest' => null,
            'language' => 'en',
            'platform_id' => 0,
            'is_premium' => false,
            'is_safe_mode' => false,
        ]);

        // Should match with the older pending user (user3)
        $match = $this->repository->findAvailableMatch($user1->gender, $user1->interest);

        expect($match)->not->toBeNull();
        expect($match->user_id)->toBe($user3->id);
    });
});
