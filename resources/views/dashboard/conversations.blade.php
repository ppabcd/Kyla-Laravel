@extends('layouts.dashboard')

@section('title', 'Conversation Analytics')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="border-b border-zinc-200 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900">Conversation Analytics</h1>
            <p class="text-zinc-600 mt-1">Monitor active conversations and chat statistics</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Active Conversations -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Active Now</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($conversationStats['active_now'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-comments text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Live
                    </span>
                    <span class="text-zinc-600 ml-2">conversations happening now</span>
                </div>
            </div>

            <!-- Today's Conversations -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Today</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($conversationStats['total_today'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-blue-600 font-medium">
                        {{ number_format(($conversationStats['total_today'] ?? 0) - ($conversationStats['total_week'] ?? 0) / 7) }}
                    </span>
                    <span class="text-zinc-600 ml-2">vs daily average</span>
                </div>
            </div>

            <!-- Weekly Conversations -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">This Week</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($conversationStats['total_week'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-indigo-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-indigo-600 font-medium">
                        {{ number_format(($conversationStats['total_week'] ?? 0) / 7, 1) }}
                    </span>
                    <span class="text-zinc-600 ml-2">average per day</span>
                </div>
            </div>

            <!-- Average Duration -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Avg Duration</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($conversationStats['avg_duration'] ?? 0, 1) }}m
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-purple-600 font-medium">
                        {{ number_format($conversationStats['success_rate'] ?? 0, 1) }}%
                    </span>
                    <span class="text-zinc-600 ml-2">success rate</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Conversation Types -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Conversation Types</h3>
                <div class="h-64">
                    <canvas id="conversationTypesChart"></canvas>
                </div>
            </div>

            <!-- Success Rate Trend -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Success Rate Trend</h3>
                <div class="h-64">
                    <canvas id="successRateChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Active Conversations Table -->
        <div class="bg-white rounded-lg shadow border border-zinc-200">
            <div class="px-6 py-4 border-b border-zinc-200">
                <h3 class="text-lg font-semibold text-zinc-900">Active Conversations</h3>
                <p class="text-sm text-zinc-600 mt-1">Currently ongoing conversations</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Participants
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Started
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Duration
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Messages
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-200">
                        @forelse($activeConversations ?? [] as $conversation)
                            <tr class="hover:bg-zinc-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex -space-x-2">
                                            <div
                                                class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ substr($conversation->first_user->first_name ?? 'U', 0, 1) }}
                                            </div>
                                            <div
                                                class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ substr($conversation->second_user->first_name ?? 'U', 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900">
                                                {{ $conversation->first_user->first_name ?? 'User' }} &
                                                {{ $conversation->second_user->first_name ?? 'User' }}
                                            </div>
                                            <div class="text-sm text-zinc-500">
                                                ID: {{ $conversation->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600">
                                    {{ $conversation->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600">
                                    {{ $conversation->created_at->diffInMinutes(now()) }}m
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600">
                                    {{ $conversation->message_count ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                                        Active
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-zinc-500">
                                    <i class="fas fa-comments text-3xl text-zinc-300 mb-3"></i>
                                    <p>No active conversations at the moment</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Conversation Types Chart
            const conversationTypesCtx = document.getElementById('conversationTypesChart').getContext('2d');
            new Chart(conversationTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($conversationStats['by_type'] ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($conversationStats['by_type'] ?? [])) !!},
                        backgroundColor: [
                            '#6366f1', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });

            // Success Rate Chart
            const successRateCtx = document.getElementById('successRateChart').getContext('2d');
            new Chart(successRateCtx, {
                type: 'line',
                data: {
                    labels: ['6 days ago', '5 days ago', '4 days ago', '3 days ago', '2 days ago', 'Yesterday', 'Today'],
                    datasets: [{
                        label: 'Success Rate %',
                        data: [65, 68, 72, 69, 75, 78, {{ $conversationStats['success_rate'] ?? 0 }}],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function (value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Auto refresh every 30 seconds
            setInterval(function () {
                location.reload();
            }, 30000);
        </script>
    @endpush
@endsection
