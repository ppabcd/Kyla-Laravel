<?php

use App\Models\ConversationLog;
use App\Models\Pair;
use App\Models\PairPending;
use App\Models\Rating;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can be created with required fields', function () {
    $user = User::factory()->create([
        'telegram_id' => 123456789,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
        'language_code' => 'en',
        'gender' => 'male',
        'interest' => 'female',
        'age' => 25,
        'is_premium' => false,
        'is_banned' => false,
        'balance' => 100,
    ]);

    expect($user->telegram_id)->toBe(123456789);
    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->username)->toBe('johndoe');
    expect($user->language_code)->toBe('en');
    expect($user->gender)->toBe('male');
    expect($user->interest)->toBe('female');
    expect($user->age)->toBe(25);
    expect($user->is_premium)->toBe(false);
    expect($user->is_banned)->toBe(false);
    expect($user->balance)->toBe(100);
});

test('user full name attribute returns correct format', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    expect($user->full_name)->toBe('John Doe');
});

test('user full name works with only first name', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => null,
    ]);

    expect($user->full_name)->toBe('John');
});

test('user display name returns username or full name', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => 'johndoe',
    ]);

    expect($user->display_name)->toBe('@johndoe');
});

test('user display name falls back to full name when no username', function () {
    $user = User::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'username' => null,
    ]);

    expect($user->display_name)->toBe('John Doe');
});

test('user is banned check returns correct boolean', function () {
    $bannedUser = User::factory()->create(['is_banned' => 1]);
    $normalUser = User::factory()->create(['is_banned' => 0]);

    expect($bannedUser->isBanned())->toBe(true);
    expect($normalUser->isBanned())->toBe(false);
});

test('user is premium check returns correct boolean', function () {
    $premiumUser = User::factory()->create(['is_premium' => 1]);
    $normalUser = User::factory()->create(['is_premium' => 0]);

    expect($premiumUser->isPremium())->toBe(true);
    expect($normalUser->isPremium())->toBe(false);
});


test('user is in safe mode check returns correct boolean', function () {
    $safeModeUser = User::factory()->create(['safe_mode' => 1]);
    $normalUser = User::factory()->create(['safe_mode' => 0]);

    expect($safeModeUser->isInSafeMode())->toBe(true);
    expect($normalUser->isInSafeMode())->toBe(false);
});


test('user current balance returns correct value', function () {
    $user = User::factory()->create(['balance' => 150]);

    expect($user->balance)->toBe(150);
});

test('user can deduct balance successfully', function () {
    $user = User::factory()->create(['balance' => 100]);

    $result = $user->decrementBalance(30);

    expect($result)->toBe(true);
    expect($user->fresh()->balance)->toBe(70);
});

test('user cannot deduct more than available balance', function () {
    $user = User::factory()->create(['balance' => 100]);

    $result = $user->decrementBalance(150);

    expect($result)->toBe(false);
    expect($user->fresh()->balance)->toBe(100);
});

test('user can add balance successfully', function () {
    $user = User::factory()->create(['balance' => 100]);

    $user->incrementBalance(50);

    expect($user->fresh()->balance)->toBe(150);
});

test('user scopes work correctly', function () {
    // Create test users with specific values
    User::factory()->create(['is_banned' => 1]);
    User::factory()->create(['is_banned' => 0]);
    User::factory()->create(['is_premium' => 1]);
    User::factory()->create(['is_premium' => 0]);
    User::factory()->create(['gender' => 'male']);
    User::factory()->create(['gender' => 'female']);
    User::factory()->create(['interest' => 'male']);
    User::factory()->create(['interest' => 'female']);
    User::factory()->create(['language_code' => 'en']);
    User::factory()->create(['language_code' => 'id']);

    expect(User::banned()->count())->toBeGreaterThanOrEqual(1);
    expect(User::premium()->count())->toBeGreaterThanOrEqual(1);
    expect(User::byGender('male')->count())->toBeGreaterThanOrEqual(1);
    expect(User::byInterest('male')->count())->toBeGreaterThanOrEqual(1);
    expect(User::byLanguage('en')->count())->toBeGreaterThanOrEqual(1);
});

test('user relationships work correctly', function () {
    $user = User::factory()->create();

    // Test pairs relationship
    $pair = Pair::factory()->create(['user_id' => $user->id]);
    expect($user->pairs)->toHaveCount(1);
    expect($user->pairs->first()->id)->toBe($pair->id);

    // Test pending pairs relationship
    $pendingPair = PairPending::factory()->create(['user_id' => $user->id]);
    expect($user->pendingPairs)->toHaveCount(1);
    expect($user->pendingPairs->first()->id)->toBe($pendingPair->id);

    // Test location relationship
    $location = UserLocation::factory()->create(['user_id' => $user->id]);
    expect($user->fresh()->userLocation->id)->toBe($location->id);

    // Test rating relationship
    $rating = Rating::factory()->create(['user_id' => $user->id]);
    expect($user->fresh()->rating->id)->toBe($rating->id);

    // Test conversation logs relationship
    $conversationLog = ConversationLog::factory()->create(['user_id' => $user->id]);
    expect($user->conversationLogs)->toHaveCount(1);
    expect($user->conversationLogs->first()->id)->toBe($conversationLog->id);
});

test('user average rating attribute works correctly', function () {
    $user = User::factory()->create();

    // Create ratings for the user (user is being rated)
    Rating::factory()->create(['rated_user_id' => $user->id, 'rating' => 4]);
    Rating::factory()->create(['rated_user_id' => $user->id, 'rating' => 5]);
    Rating::factory()->create(['rated_user_id' => $user->id, 'rating' => 3]);

    expect($user->average_rating)->toBe(4.0);
});

test('user total ratings attribute works correctly', function () {
    $user = User::factory()->create();

    // Create ratings for the user (user is being rated)
    Rating::factory()->create(['rated_user_id' => $user->id]);
    Rating::factory()->create(['rated_user_id' => $user->id]);
    Rating::factory()->create(['rated_user_id' => $user->id]);

    expect($user->total_ratings)->toBe(3);
});
