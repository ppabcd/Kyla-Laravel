# Clean Architecture & Repository Pattern - Kyla Laravel

## Overview

Kyla Laravel menggunakan Clean Architecture dan Repository Pattern untuk memisahkan concerns dan memudahkan testing serta maintenance.

## Architecture Layers

### 1. Domain Layer
Lapisan paling dalam yang berisi business logic dan entities.

```
app/Domain/
├── Entities/           # Domain entities
│   ├── User.php
│   └── Pair.php
└── Repositories/       # Repository interfaces
    ├── UserRepositoryInterface.php
    └── PairRepositoryInterface.php
```

### 2. Application Layer
Lapisan yang berisi use cases dan business logic.

```
app/Application/
└── Services/          # Application services
    ├── UserService.php
    └── MatchingService.php
```

### 3. Infrastructure Layer
Lapisan yang berisi implementasi konkret dari interfaces.

```
app/Infrastructure/
└── Repositories/      # Repository implementations
    ├── UserRepository.php
    └── PairRepository.php
```

### 4. Presentation Layer
Lapisan yang berisi controllers, commands, dan callbacks.

```
app/Telegram/
├── Commands/          # Telegram commands
├── Callbacks/         # Telegram callbacks
├── Controllers/       # HTTP controllers
└── Middleware/        # Middleware
```

## Repository Pattern

### Interface Definition

```php
interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByTelegramId(int $telegramId): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
    // ... other methods
}
```

### Implementation

```php
class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?User
    {
        return User::find($id);
    }
    
    public function findByTelegramId(int $telegramId): ?User
    {
        return User::where('telegram_id', $telegramId)->first();
    }
    
    // ... other implementations
}
```

### Dependency Injection

```php
// In ServiceProvider
$this->app->bind(UserRepositoryInterface::class, UserRepository::class);

// In Service
class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}
}
```

## Service Layer

### UserService
Mengelola business logic untuk user management.

```php
class UserService
{
    public function findOrCreateUser(array $telegramData): User
    {
        $user = $this->userRepository->findByTelegramId($telegramData['id']);
        
        if (!$user) {
            $user = $this->userRepository->create([
                'telegram_id' => $telegramData['id'],
                'first_name' => $telegramData['first_name'],
                // ... other fields
            ]);
        }
        
        return $user;
    }
    
    public function banUser(User $user, string $reason): bool
    {
        $updated = $this->userRepository->update($user, [
            'is_banned' => true,
            'banned_reason' => $reason
        ]);
        
        if ($updated) {
            // End any active pairs
            $activePair = $this->pairRepository->findActivePairByUserId($user->id);
            if ($activePair) {
                $this->pairRepository->endPair($activePair, $user->id, 'User banned');
            }
        }
        
        return $updated;
    }
}
```

### MatchingService
Mengelola business logic untuk matching algorithm.

```php
class MatchingService
{
    public function findMatch(User $user): ?User
    {
        if (!$user->canMatch()) {
            return null;
        }
        
        $matchableUsers = $this->userService->findMatchableUsers($user);
        return $this->selectBestMatch($user, $matchableUsers);
    }
    
    public function createPair(User $user1, User $user2): ?Pair
    {
        $pair = $this->pairRepository->create([
            'first_user_id' => $user1->id,
            'second_user_id' => $user2->id,
            'status' => 'active',
            'started_at' => now(),
        ]);
        
        if ($pair) {
            $this->notifyUsersOfMatch($user1, $user2, $pair);
        }
        
        return $pair;
    }
}
```

## Domain Entities

### User Entity
```php
class User extends Model
{
    protected $fillable = [
        'telegram_id',
        'first_name',
        'last_name',
        'gender',
        'interest',
        'age',
        'is_premium',
        'is_banned',
        // ... other fields
    ];
    
    public function isActive(): bool
    {
        return !$this->is_banned && 
               $this->last_activity_at && 
               $this->last_activity_at->diffInHours(now()) < 24;
    }
    
    public function isPremium(): bool
    {
        return $this->is_premium && 
               (!$this->premium_expires_at || $this->premium_expires_at->isFuture());
    }
    
    public function canMatch(): bool
    {
        return $this->isActive() && 
               $this->gender && 
               $this->interest && 
               $this->age;
    }
}
```

## Benefits

### 1. Separation of Concerns
- Domain logic terpisah dari infrastructure
- Business rules tidak bergantung pada framework
- Mudah untuk testing

### 2. Testability
```php
// Mock repository untuk testing
$mockRepository = Mockery::mock(UserRepositoryInterface::class);
$mockRepository->shouldReceive('findByTelegramId')
    ->with(123456789)
    ->andReturn(new User(['id' => 1, 'telegram_id' => 123456789]));

$userService = new UserService($mockRepository);
```

### 3. Maintainability
- Perubahan database tidak mempengaruhi business logic
- Mudah untuk mengganti implementasi
- Code yang lebih clean dan readable

### 4. Scalability
- Mudah untuk menambah fitur baru
- Repository pattern memudahkan caching
- Service layer memudahkan business logic reuse

## Best Practices

### 1. Interface Segregation
```php
// Good: Specific interface
interface UserReadRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByTelegramId(int $telegramId): ?User;
}

interface UserWriteRepositoryInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
}
```

### 2. Single Responsibility
```php
// Good: Each service has one responsibility
class UserService // User management
class MatchingService // Matching algorithm
class PaymentService // Payment processing
```

### 3. Dependency Inversion
```php
// Good: Depend on abstractions, not concretions
class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository // Interface, not concrete class
    ) {}
}
```

### 4. Error Handling
```php
class UserService
{
    public function findOrCreateUser(array $telegramData): User
    {
        try {
            $user = $this->userRepository->findByTelegramId($telegramData['id']);
            
            if (!$user) {
                $user = $this->userRepository->create($telegramData);
                Log::info('New user created', ['telegram_id' => $telegramData['id']]);
            }
            
            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to find or create user', [
                'telegram_id' => $telegramData['id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
```

## Testing Strategy

### 1. Unit Tests
```php
class UserServiceTest extends TestCase
{
    public function test_find_or_create_user_creates_new_user()
    {
        $mockRepository = Mockery::mock(UserRepositoryInterface::class);
        $mockRepository->shouldReceive('findByTelegramId')
            ->with(123456789)
            ->andReturn(null);
        
        $mockRepository->shouldReceive('create')
            ->with(['telegram_id' => 123456789, 'first_name' => 'John'])
            ->andReturn(new User(['id' => 1]));
        
        $userService = new UserService($mockRepository);
        $user = $userService->findOrCreateUser([
            'id' => 123456789,
            'first_name' => 'John'
        ]);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->id);
    }
}
```

### 2. Integration Tests
```php
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_find_by_telegram_id_returns_user()
    {
        $user = User::factory()->create(['telegram_id' => 123456789]);
        
        $repository = new UserRepository();
        $foundUser = $repository->findByTelegramId(123456789);
        
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
}
```

## Conclusion

Clean Architecture dan Repository Pattern memberikan struktur yang solid untuk aplikasi Kyla Laravel. Dengan pemisahan yang jelas antara layers, aplikasi menjadi lebih maintainable, testable, dan scalable. 
