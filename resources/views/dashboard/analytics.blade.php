@extends('layouts.dashboard')

@section('title', 'System Analytics')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="border-b border-zinc-200 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900">System Analytics</h1>
            <p class="text-zinc-600 mt-1">Advanced analytics and performance metrics</p>
        </div>

        <!-- Performance Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- System Uptime -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">System Uptime</p>
                        <p class="text-2xl font-bold text-zinc-900">99.9%</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-server text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">
                        <i class="fas fa-check-circle mr-1"></i>
                        Healthy
                    </span>
                    <span class="text-zinc-600 ml-2">last 30 days</span>
                </div>
            </div>

            <!-- API Response Time -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Avg Response Time</p>
                        <p class="text-2xl font-bold text-zinc-900">245ms</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-blue-600 font-medium">
                        -12ms
                    </span>
                    <span class="text-zinc-600 ml-2">vs last week</span>
                </div>
            </div>

            <!-- Database Performance -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">DB Query Time</p>
                        <p class="text-2xl font-bold text-zinc-900">89ms</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-database text-indigo-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-indigo-600 font-medium">
                        Optimal
                    </span>
                    <span class="text-zinc-600 ml-2">performance</span>
                </div>
            </div>

            <!-- Cache Hit Rate -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Cache Hit Rate</p>
                        <p class="text-2xl font-bold text-zinc-900">94.2%</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-memory text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-purple-600 font-medium">
                        +2.1%
                    </span>
                    <span class="text-zinc-600 ml-2">improvement</span>
                </div>
            </div>
        </div>

        <!-- Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- User Growth Trends -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">User Growth Analysis</h3>
                <div class="h-64">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>

            <!-- Engagement Metrics -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">User Engagement</h3>
                <div class="h-64">
                    <canvas id="engagementChart"></canvas>
                </div>
            </div>
        </div>

        <!-- System Health Status -->
        <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
            <h3 class="text-lg font-semibold text-zinc-900 mb-4">System Health Overview</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-database text-green-600 text-2xl"></i>
                    </div>
                    <h4 class="font-medium text-zinc-900">Database</h4>
                    <p class="text-sm text-green-600 font-medium">Healthy</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-memory text-blue-600 text-2xl"></i>
                    </div>
                    <h4 class="font-medium text-zinc-900">Cache System</h4>
                    <p class="text-sm text-blue-600 font-medium">Online</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-tasks text-purple-600 text-2xl"></i>
                    </div>
                    <h4 class="font-medium text-zinc-900">Queue System</h4>
                    <p class="text-sm text-purple-600 font-medium">Running</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // User Growth Chart
            const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
            new Chart(userGrowthCtx, {
                type: 'line',
                data: {
                    labels: ['30 days ago', '25 days ago', '20 days ago', '15 days ago', '10 days ago', '5 days ago', 'Today'],
                    datasets: [{
                        label: 'Total Users',
                        data: [1200, 1350, 1480, 1620, 1750, 1890, 2000],
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
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Engagement Chart
            const engagementCtx = document.getElementById('engagementChart').getContext('2d');
            new Chart(engagementCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Daily Active', 'Weekly Active', 'Monthly Active', 'Inactive'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#10b981', '#6366f1', '#f59e0b', '#ef4444'],
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
        </script>
    @endpush
@endsection
