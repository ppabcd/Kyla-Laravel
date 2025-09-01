<?php

use App\Models\ConversationLog;
use App\Models\Media;
use App\Models\Message;
use App\Models\Pair;
use App\Models\PairPending;
use App\Models\Rating;
use App\Models\Session;
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
        'is_bot' => false,
        'gender' => 1,
        'interest' => 2,
        'age' => 25,
        'premium' => false,
        'banned' => false,
        'balances' => 100,
    ]);

    expect($user->telegram_id)->toBe(123456789);
    expect($user->first_name)->toBe('John');
    expect($user->last_name)->toBe('Doe');
    expect($user->username)->toBe('johndoe');
    expect($user->language_code)->toBe('en');
    expect($user->is_bot)->toBe(false);
    expect($user->gender)->toBe(1);
    expect($user->interest)->toBe(2);
    expect($user->age)->toBe(25);
    expect($user->premium)->toBe(false);
    expect($user->banned)->toBe(false);
    expect($user->balances)->toBe(100);
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
    $bannedUser = User::factory()->create(['banned' => 1]);
    $normalUser = User::factory()->create(['banned' => 0]);

    expect($bannedUser->isBanned())->toBe(true);
    expect($normalUser->isBanned())->toBe(false);
});

test('user is premium check returns correct boolean', function () {
    $premiumUser = User::factory()->create(['premium' => 1]);
    $normalUser = User::factory()->create(['premium' => 0]);

    expect($premiumUser->isPremium())->toBe(true);
    expect($normalUser->isPremium())->toBe(false);
});

test('user is blocked check returns correct boolean', function () {
    $blockedUser = User::factory()->create(['is_blocked' => 1]);
    $normalUser = User::factory()->create(['is_blocked' => 0]);

    expect($blockedUser->isBlocked())->toBe(true);
    expect($normalUser->isBlocked())->toBe(false);
});

test('user is in safe mode check returns correct boolean', function () {
    $safeModeUser = User::factory()->create(['is_safe_mode' => 1]);
    $normalUser = User::factory()->create(['is_safe_mode' => 0]);

    expect($safeModeUser->isInSafeMode())->toBe(true);
    expect($normalUser->isInSafeMode())->toBe(false);
});

test('user is new check returns correct boolean', function () {
    $newUser = User::factory()->create(['is_new_user' => true]);
    $oldUser = User::factory()->create(['is_new_user' => false]);

    expect($newUser->isNew())->toBe(true);
    expect($oldUser->isNew())->toBe(false);
});

test('user current balance returns correct value', function () {
    $user = User::factory()->create(['balances' => 150]);

    expect($user->current_balance)->toBe(150);
});

test('user has balance check works correctly', function () {
    $user = User::factory()->create(['balances' => 100]);

    expect($user->hasBalance(50))->toBe(true);
    expect($user->hasBalance(100))->toBe(true);
    expect($user->hasBalance(150))->toBe(false);
});

test('user can deduct balance successfully', function () {
    $user = User::factory()->create(['balances' => 100]);

    $result = $user->deductBalance(30);

    expect($result)->toBe(true);
    expect($user->fresh()->balances)->toBe(70);
});

test('user cannot deduct more than available balance', function () {
    $user = User::factory()->create(['balances' => 100]);

    $result = $user->deductBalance(150);

    expect($result)->toBe(false);
    expect($user->fresh()->balances)->toBe(100);
});

test('user can add balance successfully', function () {
    $user = User::factory()->create(['balances' => 100]);

    $result = $user->addBalance(50);

    expect($result)->toBe(true);
    expect($user->fresh()->balances)->toBe(150);
});

test('user scopes work correctly', function () {
    // Create test users
    User::factory()->create(['banned' => 1]);
    User::factory()->create(['banned' => 0]);
    User::factory()->create(['premium' => 1]);
    User::factory()->create(['premium' => 0]);
    User::factory()->create(['gender' => 1]);
    User::factory()->create(['gender' => 2]);
    User::factory()->create(['interest' => 1]);
    User::factory()->create(['interest' => 2]);
    User::factory()->create(['language_code' => 'en']);
    User::factory()->create(['language_code' => 'id']);
    User::factory()->create(['is_new_user' => true]);
    User::factory()->create(['is_new_user' => false]);

    expect(User::banned()->count())->toBe(1);
    expect(User::premium()->count())->toBe(1);
    expect(User::byGender(1)->count())->toBe(1);
    expect(User::byInterest(1)->count())->toBe(1);
    expect(User::byLanguage('en')->count())->toBe(1);
    expect(User::new()->count())->toBe(1);
});

test('user relationships work correctly', function () {
    $user = User::factory()->create();

    // Test pairs relationship
    $pair = Pair::factory()->create(['first_user_id' => $user->id]);
    expect($user->pairsAsFirst)->toHaveCount(1);
    expect($user->pairsAsFirst->first()->id)->toBe($pair->id);

    // Test pending pairs relationship
    $pendingPair = PairPending::factory()->create(['user_id' => $user->id]);
    expect($user->pendingPairs)->toHaveCount(1);
    expect($user->pendingPairs->first()->id)->toBe($pendingPair->id);

    // Test messages relationship
    $message = Message::factory()->create(['user_id' => $user->id]);
    expect($user->messages)->toHaveCount(1);
    expect($user->messages->first()->id)->toBe($message->id);

    // Test media relationship
    $media = Media::factory()->create(['user_id' => $user->id]);
    expect($user->media)->toHaveCount(1);
    expect($user->media->first()->id)->toBe($media->id);

    // Test session relationship
    $session = Session::factory()->create(['user_id' => $user->id]);
    expect($user->session->id)->toBe($session->id);

    // Test location relationship
    $location = UserLocation::factory()->create(['user_id' => $user->id]);
    expect($user->location->id)->toBe($location->id);

    // Test rating relationship
    $rating = Rating::factory()->create(['user_id' => $user->id]);
    expect($user->rating->id)->toBe($rating->id);

    // Test conversation logs relationship
    $conversationLog = ConversationLog::factory()->create(['user_id' => $user->id]);
    expect($user->conversationLogs)->toHaveCount(1);
    expect($user->conversationLogs->first()->id)->toBe($conversationLog->id);
});

test('user average rating attribute works correctly', function () {
    $user = User::factory()->create();

    // Create ratings for the user
    Rating::factory()->create(['user_id' => $user->id, 'rating' => 4]);
    Rating::factory()->create(['user_id' => $user->id, 'rating' => 5]);
    Rating::factory()->create(['user_id' => $user->id, 'rating' => 3]);

    expect($user->average_rating)->toBe(4.0);
});

test('user total ratings attribute works correctly', function () {
    $user = User::factory()->create();

    // Create ratings for the user
    Rating::factory()->create(['user_id' => $user->id]);
    Rating::factory()->create(['user_id' => $user->id]);
    Rating::factory()->create(['user_id' => $user->id]);

    expect($user->total_ratings)->toBe(3);
});
