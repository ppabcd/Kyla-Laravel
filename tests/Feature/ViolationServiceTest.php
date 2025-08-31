<?php

use App\Application\Services\ViolationService;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->violationService = app(ViolationService::class);
    $this->user = User::factory()->create();
});

test('detects username promotion correctly', function () {
    expect($this->violationService->detectPromotion('Hello @john123'))->toBeTrue();
    expect($this->violationService->detectPromotion('Contact me @myusername'))->toBeTrue();
    expect($this->violationService->detectPromotion('My username is @test'))->toBeTrue();
});

test('detects telegram links correctly', function () {
    expect($this->violationService->detectPromotion('Join me at t.me/mychannel'))->toBeTrue();
    expect($this->violationService->detectPromotion('Find me telegram.me/user123'))->toBeTrue();
});

test('detects promotional phrases correctly', function () {
    expect($this->violationService->detectPromotion('follow me please'))->toBeTrue();
    expect($this->violationService->detectPromotion('add me on telegram'))->toBeTrue();
    expect($this->violationService->detectPromotion('contact me for more'))->toBeTrue();
    expect($this->violationService->detectPromotion('find me on social media'))->toBeTrue();
});

test('detects spam patterns correctly', function () {
    expect($this->violationService->detectPromotion('aaaaaaaa'))->toBeTrue();
    expect($this->violationService->detectPromotion('11111111'))->toBeTrue();
});

test('allows normal conversation', function () {
    expect($this->violationService->detectPromotion('Hello how are you?'))->toBeFalse();
    expect($this->violationService->detectPromotion('Nice to meet you!'))->toBeFalse();
    expect($this->violationService->detectPromotion('What do you like to do?'))->toBeFalse();
});

test('records violation correctly', function () {
    $violation = $this->violationService->recordViolation(
        $this->user,
        'Hello @spam123',
        'promotion'
    );

    expect($violation)->toBeInstanceOf(Violation::class);
    expect($violation->user_id)->toBe($this->user->id);
    expect($violation->violation_type)->toBe('promotion');
    expect($violation->content)->toBe('Hello @spam123');
});

test('applies soft ban after repeated violations', function () {
    // Create 2 existing violations in the last hour
    Violation::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'violation_type' => 'promotion',
        'detected_at' => now()->subMinutes(30),
    ]);

    // This should trigger a soft ban (3rd violation)
    $wasBanned = $this->violationService->checkAndApplySoftBan($this->user, 'Contact @me123');

    expect($wasBanned)->toBeTrue();
    expect($this->user->fresh()->isSoftBanned())->toBeTrue();
});

test('does not ban for first two violations', function () {
    // First violation
    $wasBanned1 = $this->violationService->checkAndApplySoftBan($this->user, 'Hello @test1');
    expect($wasBanned1)->toBeFalse();

    // Second violation
    $wasBanned2 = $this->violationService->checkAndApplySoftBan($this->user->fresh(), 'Add me @test2');
    expect($wasBanned2)->toBeFalse();

    expect($this->user->fresh()->isSoftBanned())->toBeFalse();
});

test('calculates remaining ban time correctly', function () {
    $this->user->applySoftBan(30, 'Test ban');

    $remaining = $this->violationService->getSoftBanRemainingMinutes($this->user);

    expect($remaining)->toBeGreaterThan(25);
    expect($remaining)->toBeLessThanOrEqual(30);
});
