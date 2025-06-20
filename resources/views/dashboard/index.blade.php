@extends('layouts.dashboard')

@section('title', 'Dashboard - Kyla Bot')
@section('page-title', 'Dashboard Overview')

@section('content')
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Users -->
            <div
                class="stat-card bg-white rounded-lg shadow-sm border border-zinc-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Total Users</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($stats['total_users'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span
                        class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/20 dark:text-green-400">
                        <i class="fas fa-arrow-up mr-1"></i>
                        +{{ $stats['new_users_today'] ?? 0 }} today
                    </span>
                </div>
            </div>

            <!-- Active Conversations -->
            <div
                class="stat-card bg-white rounded-lg shadow-sm border border-zinc-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Active Conversations</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($stats['active_conversations'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/20">
                        <i class="fas fa-comments text-green-600 dark:text-green-400"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span
                        class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                        {{ $stats['total_conversations_today'] ?? 0 }} started today
                    </span>
                </div>
            </div>

            <!-- Queue Length -->
            <div
                class="stat-card bg-white rounded-lg shadow-sm border border-zinc-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Search Queue</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($stats['queue_length'] ?? 0) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/20">
                        <i class="fas fa-clock text-orange-600 dark:text-orange-400"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span
                        class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400">
                        Waiting for match
                    </span>
                </div>
            </div>

            <!-- Revenue Today -->
            <div
                class="stat-card bg-white rounded-lg shadow-sm border border-zinc-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Revenue Today</p>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">
                            ${{ number_format($stats['revenue_today'] ?? 0, 2) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <i class="fas fa-dollar-sign text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                        ${{ number_format($stats['revenue_week'] ?? 0, 2) }} this week
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Growth Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">User Growth</h3>
                    <div class="flex space-x-2">
                        <button
                            class="px-3 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md hover:bg-zinc-50">7D</button>
                        <button
                            class="px-3 py-1 text-xs font-medium text-white bg-indigo-600 border border-indigo-600 rounded-md">30D</button>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Conversation Trends -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Conversation Trends</h3>
                    <div class="flex items-center space-x-2">
                        <span
                            class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                            Active
                        </span>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="conversationChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity and System Status -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Recent Users</h3>
                <div class="space-y-3">
                    @foreach(($recentActivity['recent_users'] ?? []) as $user)
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                    <span
                                        class="text-white text-sm font-medium">{{ strtoupper(substr($user->first_name ?? 'U', 0, 1)) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 truncate">{{ $user->first_name ?? 'Unknown' }}</p>
                                <p class="text-xs text-zinc-500">
                                    {{ $user->created_at ? $user->created_at->diffForHumans() : 'Recently' }}</p>
                            </div>
                            <div class="flex-shrink-0">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                    {{ ($user->is_premium ?? false) ? 'bg-yellow-50 text-yellow-700' : 'bg-zinc-50 text-zinc-700' }}">
                                    {{ ($user->is_premium ?? false) ? 'Premium' : 'Regular' }}
                                </span>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($recentActivity['recent_users'] ?? []))
                        <div class="text-center py-4">
                            <p class="text-sm text-zinc-500">No recent users</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Health -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">System Health</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-database text-zinc-400"></i>
                            <span class="text-sm text-zinc-600">Database</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-xs text-green-600 font-medium">Healthy</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-memory text-zinc-400"></i>
                            <span class="text-sm text-zinc-600">Cache</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="flex h-2 w-2">
                                <span
                                    class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span class="text-xs text-green-600 font-medium">Healthy</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-clock text-zinc-400"></i>
                            <span class="text-sm text-zinc-600">API Response</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                class="text-xs text-zinc-600">{{ $stats['system_health']['api_response_time'] ?? 0 }}ms</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-exclamation-triangle text-zinc-400"></i>
                            <span class="text-sm text-zinc-600">Pending Reports</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                {{ ($stats['pending_reports'] ?? 0) > 0 ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                                {{ $stats['pending_reports'] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('dashboard.users') }}"
                        class="flex items-center space-x-3 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users text-indigo-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900">Manage Users</p>
                            <p class="text-xs text-zinc-500">View and manage user accounts</p>
                        </div>
                    </a>

                    <a href="{{ route('dashboard.moderation') }}"
                        class="flex items-center space-x-3 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-shield-alt text-red-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900">Moderation</p>
                            <p class="text-xs text-zinc-500">Handle reports and bans</p>
                        </div>
                    </a>

                    <a href="{{ route('dashboard.finances') }}"
                        class="flex items-center space-x-3 p-3 rounded-lg border border-zinc-200 hover:bg-zinc-50 transition-colors">
                        <div class="flex-shrink-0">
                            <i class="fas fa-dollar-sign text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-zinc-900">Financial Report</p>
                            <p class="text-xs text-zinc-500">View revenue and transactions</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            const userGrowthChart = new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_column($charts['user_growth'] ?? [], 'date')) !!},
                    datasets: [{
                        label: 'New Users',
                        data: {!! json_encode(array_column($charts['user_growth'] ?? [], 'users')) !!},
                        borderColor: 'rgb(79, 70, 229)',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Conversation Trends Chart
            const conversationCtx = document.getElementById('conversationChart').getContext('2d');
            const conversationChart = new Chart(conversationCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_column($charts['conversation_trends'] ?? [], 'date')) !!},
                    datasets: [{
                        label: 'Conversations',
                        data: {!! json_encode(array_column($charts['conversation_trends'] ?? [], 'conversations')) !!},
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Auto-refresh stats every 30 seconds
            setInterval(function () {
                fetch('/dashboard/api/stats')
                    .then(response => response.json())
                    .then(data => {
                        // Update stats cards
                        document.querySelector('[data-stat="total_users"]').textContent = data.total_users.toLocaleString();
                        document.querySelector('[data-stat="active_conversations"]').textContent = data.active_conversations.toLocaleString();
                        document.querySelector('[data-stat="queue_length"]').textContent = data.queue_length.toLocaleString();
                        document.querySelector('[data-stat="revenue_today"]').textContent = '$' + data.revenue_today.toFixed(2);
                    })
                    .catch(error => console.error('Error fetching stats:', error));
            }, 30000);
        });
    </script>
@endpush
