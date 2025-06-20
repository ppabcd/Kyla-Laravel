<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kyla Bot Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .sidebar-item:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        }

        .stat-card {
            transition: transform 0.2s ease-in-out;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-50 font-sans">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
            :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

            <!-- Logo -->
            <div class="flex items-center justify-center h-16 px-4 gradient-bg">
                <h1 class="text-xl font-bold text-white">
                    <i class="fab fa-telegram-plane mr-2"></i>
                    Kyla Dashboard
                </h1>
            </div>

            <!-- Navigation -->
            <nav class="mt-8 px-4">
                <div class="space-y-2">
                    <a href="{{ route('dashboard.index') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.index') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('dashboard.users') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.users') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span class="font-medium">Users</span>
                    </a>

                    <a href="{{ route('dashboard.conversations') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.conversations') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-comments w-5 h-5 mr-3"></i>
                        <span class="font-medium">Conversations</span>
                    </a>

                    <a href="{{ route('dashboard.finances') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.finances') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-dollar-sign w-5 h-5 mr-3"></i>
                        <span class="font-medium">Finances</span>
                    </a>

                    <a href="{{ route('dashboard.moderation') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.moderation') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-shield-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Moderation</span>
                    </a>

                    <a href="{{ route('dashboard.analytics') }}"
                        class="sidebar-item flex items-center px-4 py-3 text-gray-700 rounded-lg hover:text-indigo-600 {{ request()->routeIs('dashboard.analytics') ? 'bg-indigo-50 text-indigo-600 border-r-2 border-indigo-600' : '' }}">
                        <i class="fas fa-chart-line w-5 h-5 mr-3"></i>
                        <span class="font-medium">Analytics</span>
                    </a>
                </div>

                <!-- System Status -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="px-4">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">System</h3>
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Database</span>
                                <span class="flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Cache</span>
                                <span class="flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Mobile menu button -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars w-6 h-6"></i>
                    </button>

                    <!-- Page Title -->
                    <h2 class="text-xl font-semibold text-gray-800">
                        @yield('page-title', 'Dashboard')
                    </h2>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="relative text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bell w-6 h-6"></i>
                            <span
                                class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                        </button>

                        <!-- User Avatar -->
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">A</span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">Admin</span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto px-6 py-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
        x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-black bg-opacity-25 lg:hidden" x-cloak></div>

    @stack('scripts')
</body>

</html>
