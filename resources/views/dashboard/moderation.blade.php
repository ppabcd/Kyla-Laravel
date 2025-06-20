@extends('layouts.dashboard')

@section('title', 'Moderation Panel')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="border-b border-zinc-200 pb-4">
            <h1 class="text-2xl font-bold text-zinc-900">Moderation Panel</h1>
            <p class="text-zinc-600 mt-1">Monitor reports, banned users, and moderation statistics</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Pending Reports -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Pending Reports</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($moderationStats['pending_reports'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-yellow-600 font-medium">
                        <i class="fas fa-clock mr-1"></i>
                        Awaiting Review
                    </span>
                </div>
            </div>

            <!-- Resolved Today -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Resolved Today</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($moderationStats['resolved_reports_today'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-600 font-medium">
                        +{{ number_format($moderationStats['resolved_reports_today'] ?? 0) }}
                    </span>
                    <span class="text-zinc-600 ml-2">reports handled</span>
                </div>
            </div>

            <!-- Banned Users -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Banned Users</p>
                        <p class="text-2xl font-bold text-zinc-900">
                            {{ number_format($moderationStats['banned_users'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-slash text-red-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-red-600 font-medium">
                        +{{ number_format($moderationStats['banned_today'] ?? 0) }}
                    </span>
                    <span class="text-zinc-600 ml-2">banned today</span>
                </div>
            </div>

            <!-- Auto Bans -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-600">Auto Bans (7d)</p>
                        <p class="text-2xl font-bold text-zinc-900">{{ number_format($moderationStats['auto_bans'] ?? 0) }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-robot text-purple-600 text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-purple-600 font-medium">
                        Automated
                    </span>
                    <span class="text-zinc-600 ml-2">system actions</span>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Report Types -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Report Types Distribution</h3>
                <div class="h-64">
                    <canvas id="reportTypesChart"></canvas>
                </div>
            </div>

            <!-- Moderation Activity -->
            <div class="bg-white rounded-lg shadow border border-zinc-200 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 mb-4">Moderation Activity (7 days)</h3>
                <div class="h-64">
                    <canvas id="moderationActivityChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Reports and Banned Users -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Reports -->
            <div class="bg-white rounded-lg shadow border border-zinc-200">
                <div class="px-6 py-4 border-b border-zinc-200">
                    <h3 class="text-lg font-semibold text-zinc-900">Recent Reports</h3>
                    <p class="text-sm text-zinc-600 mt-1">Latest user reports requiring attention</p>
                </div>
                <div class="divide-y divide-zinc-200 max-h-96 overflow-y-auto">
                    @forelse($recentReports ?? [] as $report)
                        <div class="p-6 hover:bg-zinc-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-zinc-900">
                                            {{ $report->reporter->first_name ?? 'Unknown' }}
                                        </span>
                                        <span class="text-zinc-500">reported</span>
                                        <span class="text-sm font-medium text-zinc-900">
                                            {{ $report->reportedUser->first_name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-zinc-600 mt-1">
                                        <span class="font-medium">{{ ucfirst($report->reason) }}:</span>
                                        {{ $report->description ?? 'No description provided' }}
                                    </p>
                                    <p class="text-xs text-zinc-500 mt-2">
                                        {{ $report->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 ml-4">
                                    @if($report->status === 'pending')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($report->status === 'resolved')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Resolved
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    @endif
                                    <div class="flex space-x-1">
                                        <button class="text-indigo-600 hover:text-indigo-900 p-1">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button class="text-green-600 hover:text-green-900 p-1">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                        <button class="text-red-600 hover:text-red-900 p-1">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            <i class="fas fa-flag text-3xl text-zinc-300 mb-3"></i>
                            <p>No recent reports</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Banned Users -->
            <div class="bg-white rounded-lg shadow border border-zinc-200">
                <div class="px-6 py-4 border-b border-zinc-200">
                    <h3 class="text-lg font-semibold text-zinc-900">Recently Banned Users</h3>
                    <p class="text-sm text-zinc-600 mt-1">Users who have been banned from the platform</p>
                </div>
                <div class="divide-y divide-zinc-200 max-h-96 overflow-y-auto">
                    @forelse($bannedUsers ?? [] as $user)
                        <div class="p-6 hover:bg-zinc-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white font-medium">
                                        {{ substr($user->first_name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-zinc-900">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="text-sm text-zinc-500">
                                            @{{ $user->username ?? 'N/A' }} • ID: {{ $user->telegram_id }}
                                        </div>
                                        <div class="text-xs text-zinc-500 mt-1">
                                            Banned {{ $user->banned_at?->diffForHumans() ?? 'recently' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <div class="w-1.5 h-1.5 bg-red-400 rounded-full mr-1.5"></div>
                                        Banned
                                    </span>
                                    <div class="flex space-x-1">
                                        <button class="text-indigo-600 hover:text-indigo-900 p-1">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button class="text-green-600 hover:text-green-900 p-1" title="Unban">
                                            <i class="fas fa-user-check text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            <i class="fas fa-user-slash text-3xl text-zinc-300 mb-3"></i>
                            <p>No banned users</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Word Filter Management -->
        <div class="bg-white rounded-lg shadow border border-zinc-200">
            <div class="px-6 py-4 border-b border-zinc-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900">Word Filter Management</h3>
                        <p class="text-sm text-zinc-600 mt-1">Manage filtered words and content moderation</p>
                    </div>
                    <button id="addWordBtn" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Word
                    </button>
                </div>
            </div>

            <!-- Word Filter Statistics -->
            <div class="px-6 py-4 border-b border-zinc-200">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-zinc-900">{{ number_format($wordFilterStats['total_words'] ?? 0) }}</div>
                        <div class="text-sm text-zinc-600">Total Words</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ number_format($wordFilterStats['ai_checked'] ?? 0) }}</div>
                        <div class="text-sm text-zinc-600">AI Checked</div>
                    </div>
                    @if(isset($wordFilterStats['by_type']))
                        @foreach($wordFilterStats['by_type'] as $typeName => $count)
                            <div class="text-center">
                                <div class="text-2xl font-bold text-zinc-900">{{ number_format($count) }}</div>
                                <div class="text-sm text-zinc-600">{{ $typeName }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Word Filter Controls -->
            <div class="px-6 py-4 border-b border-zinc-200">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-64">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Search Words</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search filtered words..." 
                               class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="min-w-40">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Word Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Types</option>
                            @php
                                use App\Domain\Entities\WordFilter;
                                $wordTypes = WordFilter::getWordTypes();
                            @endphp
                            @foreach($wordTypes as $typeId => $typeName)
                                <option value="{{ $typeId }}" {{ request('type') == $typeId ? 'selected' : '' }}>
                                    {{ $typeName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-32">
                        <label class="block text-sm font-medium text-zinc-700 mb-1">AI Check</label>
                        <select name="ai_check" class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="1" {{ request('ai_check') === '1' ? 'selected' : '' }}>AI Checked</option>
                            <option value="0" {{ request('ai_check') === '0' ? 'selected' : '' }}>Not AI Checked</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-zinc-600 text-white px-4 py-2 rounded-lg hover:bg-zinc-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <button type="button" id="bulkImportBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                            <i class="fas fa-upload mr-2"></i>Bulk Import
                        </button>
                    </div>
                </form>
            </div>

            <!-- Word Filter List -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Word</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">AI Check</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-zinc-200">
                        @forelse($wordFilters as $wordFilter)
                            <tr class="hover:bg-zinc-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-zinc-900">
                                        {{ $wordFilter->word }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $wordFilter->word_type_color }}-100 text-{{ $wordFilter->word_type_color }}-800">
                                        {{ $wordFilter->word_type_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($wordFilter->is_open_ai_check)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>AI Checked
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800">
                                            Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-500">
                                    {{ $wordFilter->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button onclick="editWord({{ $wordFilter->id }}, '{{ $wordFilter->word }}', {{ $wordFilter->word_type }}, {{ $wordFilter->is_open_ai_check ? 'true' : 'false' }})" 
                                                class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteWord({{ $wordFilter->id }})" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-zinc-500">
                                    <i class="fas fa-filter text-3xl text-zinc-300 mb-3"></i>
                                    <p>No word filters found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($wordFilters->hasPages())
                <div class="px-6 py-4 border-t border-zinc-200">
                    {{ $wordFilters->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Word Modal -->
    <div id="wordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-zinc-200">
                    <h3 id="modalTitle" class="text-lg font-semibold text-zinc-900">Add Word Filter</h3>
                </div>
                <form id="wordForm" class="p-6 space-y-4">
                    <input type="hidden" id="wordId" name="wordId">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Word</label>
                        <input type="text" id="wordInput" name="word" required 
                               class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Word Type</label>
                        <select id="wordTypeInput" name="word_type" required 
                                class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($wordTypes as $typeId => $typeName)
                                <option value="{{ $typeId }}">{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" id="aiCheckInput" name="is_open_ai_check" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-zinc-300 rounded">
                        <label for="aiCheckInput" class="ml-2 block text-sm text-zinc-900">
                            AI Checked
                        </label>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeWordModal()" 
                                class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal -->
    <div id="bulkImportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-zinc-200">
                    <h3 class="text-lg font-semibold text-zinc-900">Bulk Import Words</h3>
                </div>
                <form id="bulkImportForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Word Type</label>
                        <select id="bulkWordType" name="word_type" required 
                                class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($wordTypes as $typeId => $typeName)
                                <option value="{{ $typeId }}">{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1">Words</label>
                        <textarea id="bulkWordsInput" name="words" rows="6" required placeholder="Enter words separated by comma or new line..."
                                  class="w-full px-3 py-2 border border-zinc-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        <p class="text-xs text-zinc-500 mt-1">Separate words with commas or new lines</p>
                    </div>
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" onclick="closeBulkImportModal()" 
                                class="px-4 py-2 text-sm font-medium text-zinc-700 bg-white border border-zinc-300 rounded-lg hover:bg-zinc-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Report Types Chart
            const reportTypesCtx = document.getElementById('reportTypesChart').getContext('2d');
            new Chart(reportTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_keys($moderationStats['report_types'] ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($moderationStats['report_types'] ?? [])) !!},
                        backgroundColor: [
                            '#ef4444', '#f97316', '#f59e0b', '#84cc16', '#06b6d4', '#8b5cf6'
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

            // Moderation Activity Chart
            const moderationActivityCtx = document.getElementById('moderationActivityChart').getContext('2d');
            new Chart(moderationActivityCtx, {
                type: 'bar',
                data: {
                    labels: ['6 days ago', '5 days ago', '4 days ago', '3 days ago', '2 days ago', 'Yesterday', 'Today'],
                    datasets: [{
                        label: 'Reports Resolved',
                        data: [12, 8, 15, 10, 18, 14, {{ $moderationStats['resolved_reports_today'] ?? 0 }}],
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    }, {
                        label: 'Users Banned',
                        data: [3, 2, 5, 1, 4, 2, {{ $moderationStats['banned_today'] ?? 0 }}],
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
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

            // Word Filter Management JavaScript
            let isEditMode = false;
            let currentWordId = null;

            // Add Word Button
            document.getElementById('addWordBtn').addEventListener('click', function() {
                isEditMode = false;
                currentWordId = null;
                document.getElementById('modalTitle').textContent = 'Add Word Filter';
                document.getElementById('wordForm').reset();
                document.getElementById('wordId').value = '';
                document.getElementById('wordModal').classList.remove('hidden');
            });

            // Bulk Import Button
            document.getElementById('bulkImportBtn').addEventListener('click', function() {
                document.getElementById('bulkImportForm').reset();
                document.getElementById('bulkImportModal').classList.remove('hidden');
            });

            // Edit Word Function
            function editWord(id, word, wordType, aiCheck) {
                isEditMode = true;
                currentWordId = id;
                document.getElementById('modalTitle').textContent = 'Edit Word Filter';
                document.getElementById('wordId').value = id;
                document.getElementById('wordInput').value = word;
                document.getElementById('wordTypeInput').value = wordType;
                document.getElementById('aiCheckInput').checked = aiCheck;
                document.getElementById('wordModal').classList.remove('hidden');
            }

            // Delete Word Function
            function deleteWord(id) {
                if (confirm('Are you sure you want to delete this word filter?')) {
                    fetch(`/dashboard/word-filters/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the word filter');
                    });
                }
            }

            // Close Word Modal
            function closeWordModal() {
                document.getElementById('wordModal').classList.add('hidden');
            }

            // Close Bulk Import Modal
            function closeBulkImportModal() {
                document.getElementById('bulkImportModal').classList.add('hidden');
            }

            // Word Form Submit
            document.getElementById('wordForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = {
                    word: formData.get('word'),
                    word_type: parseInt(formData.get('word_type')),
                    is_open_ai_check: formData.has('is_open_ai_check')
                };

                const url = isEditMode ? `/dashboard/word-filters/${currentWordId}` : '/dashboard/word-filters';
                const method = isEditMode ? 'PUT' : 'POST';

                fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the word filter');
                });
            });

            // Bulk Import Form Submit
            document.getElementById('bulkImportForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const data = {
                    words: formData.get('words'),
                    word_type: parseInt(formData.get('word_type'))
                };

                fetch('/dashboard/word-filters/bulk-import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Successfully imported ${data.imported_count} words`);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while importing words');
                });
            });

            // Close modals when clicking outside
            document.getElementById('wordModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeWordModal();
                }
            });

            document.getElementById('bulkImportModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeBulkImportModal();
                }
            });

            // Auto refresh every 30 seconds
            setInterval(function () {
                location.reload();
            }, 30000);
        </script>
    @endpush
@endsection
