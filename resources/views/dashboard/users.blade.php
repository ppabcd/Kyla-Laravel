@extends('layouts.dashboard')

@section('title', 'Users Management - Kyla Bot')
@section('page-title', 'Users Management')

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Total Users</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($userStats['total'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Active Users</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($userStats['active'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                        <i class="fas fa-user-check text-green-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Premium Users</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($userStats['premium'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-50">
                        <i class="fas fa-crown text-yellow-600"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Banned Users</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($userStats['banned'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50">
                        <i class="fas fa-user-slash text-red-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
            <form method="GET" action="{{ route('dashboard.users') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-64">
                    <label for="search" class="block text-sm font-medium text-zinc-700 mb-2">Search Users</label>
                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search by name, username, or Telegram ID..."
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="min-w-32">
                    <label for="status" class="block text-sm font-medium text-zinc-700 mb-2">Status</label>
                    <select id="status" name="status"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="banned" {{ ($filters['status'] ?? '') === 'banned' ? 'selected' : '' }}>Banned</option>
                        <option value="premium" {{ ($filters['status'] ?? '') === 'premium' ? 'selected' : '' }}>Premium
                        </option>
                    </select>
                </div>

                <div class="min-w-32">
                    <label for="gender" class="block text-sm font-medium text-zinc-700 mb-2">Gender</label>
                    <select id="gender" name="gender"
                        class="block w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Genders</option>
                        <option value="male" {{ ($filters['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm border border-zinc-200">
            <div class="px-6 py-4 border-b border-zinc-200">
                <h3 class="text-lg font-semibold text-zinc-900">Users List</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-zinc-950">
                    <thead class="text-zinc-500 border-b border-zinc-200">
                        <tr>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Gender</th>
                            <th class="px-6 py-3 font-medium">Balance</th>
                            <th class="px-6 py-3 font-medium">Joined</th>
                            <th class="px-6 py-3 font-medium">Last Active</th>
                            <th class="px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse($users as $user)
                                        <tr class="hover:bg-zinc-50">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center">
                                                            <span class="text-white text-sm font-medium">
                                                                {{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-zinc-900">{{ $user->first_name ?? 'Unknown' }}</p>
                                                        <p class="text-xs text-zinc-500">@{{ $user->username ?? 'No username' }}</p>
                                                        <p class="text-xs text-zinc-500">ID: {{ $user->telegram_id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex flex-col space-y-1">
                                                    @if($user->is_banned ?? false)
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                            <i class="fas fa-ban mr-1"></i>
                                                            Banned
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                            <i class="fas fa-check mr-1"></i>
                                                            Active
                                                        </span>
                                                    @endif

                                                    @if($user->is_premium ?? false)
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700">
                                                            <i class="fas fa-crown mr-1"></i>
                                                            Premium
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                    {{ ($user->gender ?? '') === 'male' ? 'bg-blue-50 text-blue-700' :
                            (($user->gender ?? '') === 'female' ? 'bg-pink-50 text-pink-700' : 'bg-zinc-50 text-zinc-700') }}">
                                                    @if(($user->gender ?? '') === 'male')
                                                        <i class="fas fa-mars mr-1"></i>
                                                        Male
                                                    @elseif(($user->gender ?? '') === 'female')
                                                        <i class="fas fa-venus mr-1"></i>
                                                        Female
                                                    @else
                                                        <i class="fas fa-question mr-1"></i>
                                                        Unknown
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span class="font-medium text-zinc-900">${{ number_format($user->balance ?? 0, 2) }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-zinc-600">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Unknown' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-zinc-600">{{ $user->last_activity ? $user->last_activity->diffForHumans() : 'Never' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <button onclick="viewUser({{ $user->id }})"
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                                        <i class="fas fa-eye mr-1"></i>
                                                        View
                                                    </button>

                                                    @if(!($user->is_banned ?? false))
                                                        <button onclick="banUser({{ $user->id }})"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 hover:text-red-700">
                                                            <i class="fas fa-ban mr-1"></i>
                                                            Ban
                                                        </button>
                                                    @else
                                                        <button onclick="unbanUser({{ $user->id }})"
                                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 hover:text-green-700">
                                                            <i class="fas fa-check mr-1"></i>
                                                            Unban
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i class="fas fa-users text-4xl text-zinc-300"></i>
                                        <p class="text-zinc-500">No users found</p>
                                        <p class="text-sm text-zinc-400">Try adjusting your search filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-zinc-700">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($users->onFirstPage())
                                <span
                                    class="px-3 py-2 text-sm text-zinc-400 bg-zinc-100 rounded-lg cursor-not-allowed">Previous</span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}"
                                    class="px-3 py-2 text-sm text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50">
                                    Previous
                                </a>
                            @endif

                            @foreach($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                                @if($page == $users->currentPage())
                                    <span class="px-3 py-2 text-sm text-white bg-indigo-600 rounded-lg">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}"
                                        class="px-3 py-2 text-sm text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach

                            @if($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}"
                                    class="px-3 py-2 text-sm text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50">
                                    Next
                                </a>
                            @else
                                <span class="px-3 py-2 text-sm text-zinc-400 bg-zinc-100 rounded-lg cursor-not-allowed">Next</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- User Detail Modal -->
    <div id="userModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-zinc-900" id="modal-title">User Details</h3>
                            <div class="mt-4" id="userDetails">
                                <!-- User details will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeUserModal()"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-zinc-600 text-base font-medium text-white hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function viewUser(userId) {
            // Show modal
            document.getElementById('userModal').classList.remove('hidden');

            // Load user details
            fetch(`/dashboard/users/${userId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('userDetails').innerHTML = `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Name</label>
                                <p class="mt-1 text-sm text-zinc-900">${data.first_name || 'Unknown'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Username</label>
                                <p class="mt-1 text-sm text-zinc-900">@${data.username || 'No username'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Telegram ID</label>
                                <p class="mt-1 text-sm text-zinc-900">${data.telegram_id}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Gender</label>
                                <p class="mt-1 text-sm text-zinc-900">${data.gender || 'Unknown'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Balance</label>
                                <p class="mt-1 text-sm text-zinc-900">$${data.balance || 0}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Premium</label>
                                <p class="mt-1 text-sm text-zinc-900">${data.is_premium ? 'Yes' : 'No'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Status</label>
                                <p class="mt-1 text-sm text-zinc-900">${data.is_banned ? 'Banned' : 'Active'}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700">Joined</label>
                                <p class="mt-1 text-sm text-zinc-900">${new Date(data.created_at).toLocaleDateString()}</p>
                            </div>
                        </div>
                    </div>
                `;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('userDetails').innerHTML = '<p class="text-red-600">Error loading user details</p>';
                });
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }

        function banUser(userId) {
            if (confirm('Are you sure you want to ban this user?')) {
                fetch(`/dashboard/users/${userId}/ban`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error banning user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error banning user');
                    });
            }
        }

        function unbanUser(userId) {
            if (confirm('Are you sure you want to unban this user?')) {
                fetch(`/dashboard/users/${userId}/unban`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error unbanning user');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error unbanning user');
                    });
            }
        }

        // Close modal when clicking outside
        document.getElementById('userModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeUserModal();
            }
        });
    </script>
@endpush
