@extends('layouts.dashboard')

@section('title', 'Financial Analytics - Kyla Bot')
@section('page-title', 'Financial Analytics')

@section('content')
    <div class="space-y-6">
        <!-- Financial Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Total Balance</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            ${{ number_format($financialStats['total_balance'] ?? 0, 2) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                        <i class="fas fa-wallet text-green-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-zinc-500">System-wide balance</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Revenue Today</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            ${{ number_format($financialStats['revenue_today'] ?? 0, 2) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                        <i class="fas fa-dollar-sign text-emerald-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span
                        class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Daily earnings
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Revenue This Week</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            ${{ number_format($financialStats['revenue_week'] ?? 0, 2) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                        <i class="fas fa-chart-line text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-zinc-500">Weekly performance</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Average Transaction</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            ${{ number_format($financialStats['avg_transaction'] ?? 0, 2) }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                        <i class="fas fa-exchange-alt text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-zinc-500">Per transaction</span>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-zinc-900">Revenue Trends</h3>
                <div class="flex space-x-2">
                    <button
                        class="px-3 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md hover:bg-zinc-50">7D</button>
                    <button
                        class="px-3 py-1 text-xs font-medium text-white bg-indigo-600 border border-indigo-600 rounded-md">30D</button>
                    <button
                        class="px-3 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md hover:bg-zinc-50">90D</button>
                </div>
            </div>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Top Spenders and Recent Transactions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Spenders -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Top Spenders</h3>
                <div class="space-y-4">
                    @foreach(($financialStats['top_spenders'] ?? []) as $index => $spender)
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-zinc-400' : ($index === 2 ? 'bg-amber-600' : 'bg-zinc-300')) }} rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-zinc-900">{{ $spender->first_name ?? 'Unknown User' }}</p>
                                <p class="text-xs text-zinc-500">@{{ $spender->username ?? 'No username' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-zinc-900">${{ number_format($spender->total_spent ?? 0, 2) }}</p>
                                <p class="text-xs text-zinc-500">{{ $spender->transaction_count ?? 0 }} transactions</p>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($financialStats['top_spenders'] ?? []))
                        <div class="text-center py-8">
                            <i class="fas fa-users text-4xl text-zinc-300"></i>
                            <p class="text-zinc-500 mt-2">No spending data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Recent Transactions</h3>
                <div class="space-y-3">
                    @foreach(($recentTransactions ?? []) as $transaction)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-zinc-100">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-8 h-8 {{ ($transaction->amount ?? 0) > 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                                        <i
                                            class="fas {{ ($transaction->amount ?? 0) > 0 ? 'fa-plus text-green-600' : 'fa-minus text-red-600' }} text-xs"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900">{{ $transaction->type ?? 'Unknown' }}</p>
                                    <p class="text-xs text-zinc-500">{{ $transaction->user->first_name ?? 'Unknown User' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-sm font-bold {{ ($transaction->amount ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ ($transaction->amount ?? 0) > 0 ? '+' : '' }}${{ number_format($transaction->amount ?? 0, 2) }}
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ $transaction->created_at ? $transaction->created_at->diffForHumans() : 'Recently' }}</p>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($recentTransactions ?? []))
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-4xl text-zinc-300"></i>
                            <p class="text-zinc-500 mt-2">No recent transactions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Types Analysis -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Transaction Types Pie Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Transaction Types</h3>
                <div class="h-64">
                    <canvas id="transactionTypesChart"></canvas>
                </div>
            </div>

            <!-- Monthly Revenue Comparison -->
            <div class="bg-white rounded-lg shadow-sm border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Monthly Comparison</h3>
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 rounded-lg bg-gradient-to-r from-green-50 to-emerald-50">
                        <div>
                            <p class="text-sm font-medium text-zinc-700">This Month</p>
                            <p class="text-2xl font-bold text-green-600">
                                ${{ number_format($financialStats['revenue_month'] ?? 0, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-zinc-500">vs Last Month</p>
                            <p class="text-lg font-semibold text-green-600">
                                <i class="fas fa-arrow-up mr-1"></i>
                                +12.5%
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-lg bg-blue-50">
                            <p class="text-xs text-zinc-600">Premium Subscriptions</p>
                            <p class="text-lg font-bold text-blue-600">
                                ${{ number_format(($financialStats['revenue_month'] ?? 0) * 0.7, 2) }}</p>
                        </div>
                        <div class="p-4 rounded-lg bg-purple-50">
                            <p class="text-xs text-zinc-600">Feature Purchases</p>
                            <p class="text-lg font-bold text-purple-600">
                                ${{ number_format(($financialStats['revenue_month'] ?? 0) * 0.3, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm border border-zinc-200">
            <div class="px-6 py-4 border-b border-zinc-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-zinc-900">All Transactions</h3>
                    <div class="flex space-x-2">
                        <button
                            class="px-3 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md hover:bg-zinc-50">
                            <i class="fas fa-download mr-1"></i>
                            Export CSV
                        </button>
                        <button
                            class="px-3 py-1 text-xs font-medium text-zinc-600 hover:text-zinc-900 border border-zinc-200 rounded-md hover:bg-zinc-50">
                            <i class="fas fa-filter mr-1"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-zinc-950">
                    <thead class="text-zinc-500 border-b border-zinc-200">
                        <tr>
                            <th class="px-6 py-3 font-medium">Transaction ID</th>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Type</th>
                            <th class="px-6 py-3 font-medium">Amount</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            <th class="px-6 py-3 font-medium">Date</th>
                            <th class="px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @forelse(($recentTransactions ?? []) as $transaction)
                                        <tr class="hover:bg-zinc-50">
                                            <td class="px-6 py-4">
                                                <span class="font-mono text-xs text-zinc-600">#{{ $transaction->id ?? 'N/A' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center space-x-2">
                                                    <div class="w-6 h-6 bg-indigo-500 rounded-full flex items-center justify-center">
                                                        <span class="text-white text-xs font-medium">
                                                            {{ strtoupper(substr($transaction->user->first_name ?? 'U', 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-zinc-900">
                                                            {{ $transaction->user->first_name ?? 'Unknown' }}</p>
                                                        <p class="text-xs text-zinc-500">@{{ $transaction->user->username ?? 'No username'
                                                            }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                    {{ ($transaction->type ?? '') === 'premium' ? 'bg-yellow-50 text-yellow-700' :
                            (($transaction->type ?? '') === 'feature' ? 'bg-blue-50 text-blue-700' : 'bg-zinc-50 text-zinc-700') }}">
                                                    {{ ucfirst($transaction->type ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="font-bold {{ ($transaction->amount ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ ($transaction->amount ?? 0) > 0 ? '+' : '' }}${{ number_format($transaction->amount ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                    {{ ($transaction->status ?? '') === 'completed' ? 'bg-green-50 text-green-700' :
                            (($transaction->status ?? '') === 'pending' ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700') }}">
                                                    {{ ucfirst($transaction->status ?? 'Unknown') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span
                                                    class="text-zinc-600">{{ $transaction->created_at ? $transaction->created_at->format('M d, Y H:i') : 'Unknown' }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <button onclick="viewTransaction('{{ $transaction->id }}')"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    View
                                                </button>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <i class="fas fa-receipt text-4xl text-zinc-300"></i>
                                        <p class="text-zinc-500">No transactions found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_column($revenueChart ?? [], 'date')) !!},
                    datasets: [{
                        label: 'Revenue',
                        data: {!! json_encode(array_column($revenueChart ?? [], 'revenue')) !!},
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Transactions',
                        data: {!! json_encode(array_column($revenueChart ?? [], 'transactions')) !!},
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Transaction Types Chart
            const transactionTypesCtx = document.getElementById('transactionTypesChart').getContext('2d');
            const transactionTypesChart = new Chart(transactionTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Premium Subscriptions', 'Feature Purchases', 'Balance Top-ups', 'Other'],
                    datasets: [{
                        data: [70, 20, 8, 2],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(156, 163, 175, 0.8)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        });

        function viewTransaction(transactionId) {
            // Implement transaction detail view
            alert('View transaction: ' + transactionId);
        }
    </script>
@endpush
