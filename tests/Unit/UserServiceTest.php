<?php

use App\Application\Services\UserService;
use App\Domain\Entities\User as DomainUser;
use App\Domain\Repositories\PairRepositoryInterface;
use App\Domain\Repositories\UserRepositoryInterface;

it('sets gender_icon when updating gender', function () {
    $user = new DomainUser;
    $user->id = 1;

    $userRepo = Mockery::mock(UserRepositoryInterface::class);
    $pairRepo = Mockery::mock(PairRepositoryInterface::class);

    $userRepo->shouldReceive('update')
        ->once()
        ->with($user, Mockery::on(function (array $data) {
            return ($data['gender'] ?? null) === 'male'
                && ($data['gender_icon'] ?? null) === '👨';
        }))
        ->andReturn(true);

    $service = new UserService($userRepo, $pairRepo);

    $result = $service->updateUserProfile($user, ['gender' => 'male']);

    expect($result)->toBeTrue();
});
