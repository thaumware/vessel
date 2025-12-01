<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Catalog Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #1f2937; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        @keyframes pulse-border { 0%, 100% { border-color: #3b82f6; } 50% { border-color: #60a5fa; } }
        .animate-pulse-border { animation: pulse-border 2s infinite; }
        .glass-effect { background: rgba(17, 24, 39, 0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-gray-100">
    <div x-data="adminPanel()" x-cloak class="min-h-full flex flex-col">
        
        <!-- Header -->
        <header class="glass-effect border-b border-gray-700/50 sticky top-0 z-50">
            <div class="max-w-screen-2xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center font-bold text-lg shadow-lg shadow-blue-500/20">
                            C
                        </div>
                        <div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                Catalog Admin Panel
                            </h1>
                            <p class="text-xs text-gray-400">Debug & Testing Console</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-full border border-emerald-500/20">
                            v1.0.0
                        </span>
                        <span class="px-3 py-1 bg-blue-500/10 text-blue-400 text-xs font-medium rounded-full border border-blue-500/20">
                            Laravel {{ app()->version() }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Navigation Tabs -->
        <nav class="glass-effect border-b border-gray-700/50">
            <div class="max-w-screen-2xl mx-auto px-6">
                <div class="flex gap-1">
                    <button @click="activeTab = 'tests'" 
                            :class="activeTab === 'tests' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Tests
                    </button>
                    <button @click="activeTab = 'database'" 
                            :class="activeTab === 'database' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        Database
                    </button>
                    <button @click="activeTab = 'actions'" 
                            :class="activeTab === 'actions' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Actions
                    </button>
                    <button @click="activeTab = 'info'" 
                            :class="activeTab === 'info' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Info
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 max-w-screen-2xl mx-auto w-full px-6 py-6">
            
            <!-- Tests Tab -->
            <div x-show="activeTab === 'tests'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="space-y-6">
                    <!-- Test Controls -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-white">PHPUnit Test Runner</h2>
                                <p class="text-sm text-gray-400 mt-1">Execute tests from the catalog module</p>
                            </div>
                            <div class="flex gap-3">
                                <select x-model="testFilter" class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">All Tests</option>
                                    <option value="--filter Unit">Unit Tests</option>
                                    <option value="--filter Feature">Feature Tests</option>
                                    <option value="--filter Items">Items Module</option>
                                    <option value="--filter Locations">Locations Module</option>
                                    <option value="--filter Stock">Stock Module</option>
                                    <option value="--filter Uom">UoM Module</option>
                                    <option value="--filter Taxonomy">Taxonomy Module</option>
                                    <option value="--filter Pricing">Pricing Module</option>
                                    <option value="--filter Admin">Admin Module</option>
                                </select>
                                <button @click="runTests()" 
                                        :disabled="testsRunning"
                                        :class="testsRunning ? 'bg-gray-600 cursor-not-allowed' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600'"
                                        class="px-6 py-2 rounded-lg font-medium text-white transition-all duration-200 flex items-center gap-2 shadow-lg shadow-blue-500/20">
                                    <svg x-show="!testsRunning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg x-show="testsRunning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="testsRunning ? 'Running...' : 'Run Tests'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Test Output -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <h3 class="font-medium text-white">Output</h3>
                            <div x-show="testResult" class="flex items-center gap-3">
                                <span :class="testResult?.success ? 'text-emerald-400' : 'text-red-400'" class="text-sm font-medium">
                                    <span x-text="testResult?.success ? 'PASSED' : 'FAILED'"></span>
                                </span>
                                <button @click="testOutput = ''; testResult = null" class="text-gray-400 hover:text-white text-sm">
                                    Clear
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <pre x-show="testOutput" class="bg-gray-900 rounded-xl p-4 text-sm font-mono text-gray-300 overflow-auto max-h-96 scrollbar-thin whitespace-pre-wrap" x-text="testOutput"></pre>
                            <div x-show="!testOutput" class="text-center py-12 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p>Click "Run Tests" to execute PHPUnit</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database Tab -->
            <div x-show="activeTab === 'database'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="grid grid-cols-12 gap-6">
                    <!-- Tables List -->
                    <div class="col-span-3">
                        <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden sticky top-24">
                            <div class="px-5 py-4 border-b border-gray-700/50">
                                <h3 class="font-medium text-white">Catalog Tables</h3>
                                <p class="text-xs text-gray-400 mt-1" x-text="tables.length + ' tables'"></p>
                            </div>
                            <div class="max-h-[calc(100vh-280px)] overflow-y-auto scrollbar-thin">
                                <template x-for="table in tables" :key="table.name">
                                    <button @click="selectTable(table.name)"
                                            :class="selectedTable === table.name ? 'bg-blue-600/20 border-l-2 border-blue-500' : 'hover:bg-gray-700/50 border-l-2 border-transparent'"
                                            class="w-full px-5 py-3 text-left transition-all duration-150 flex items-center justify-between group">
                                        <span class="text-sm truncate" :class="selectedTable === table.name ? 'text-blue-400 font-medium' : 'text-gray-300'" x-text="table.name"></span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-700 text-gray-400 group-hover:bg-gray-600" x-text="table.rows"></span>
                                    </button>
                                </template>
                                <div x-show="tables.length === 0" class="px-5 py-8 text-center text-gray-500 text-sm">
                                    No tables found
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Data -->
                    <div class="col-span-9">
                        <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-white" x-text="selectedTable || 'Select a table'"></h3>
                                    <p x-show="tableData.length > 0" class="text-xs text-gray-400 mt-1" x-text="tableData.length + ' rows'"></p>
                                </div>
                                <button x-show="selectedTable" @click="refreshTable()" class="text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="overflow-auto max-h-[calc(100vh-280px)] scrollbar-thin">
                                <template x-if="tableLoading">
                                    <div class="flex items-center justify-center py-16">
                                        <svg class="w-8 h-8 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </template>
                                <template x-if="!tableLoading && tableData.length > 0">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-900/50 sticky top-0">
                                            <tr>
                                                <template x-for="column in tableColumns" :key="column">
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap" x-text="column"></th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-700/50">
                                            <template x-for="(row, index) in tableData" :key="index">
                                                <tr class="hover:bg-gray-700/30 transition-colors">
                                                    <template x-for="column in tableColumns" :key="column">
                                                        <td class="px-4 py-3 text-gray-300 whitespace-nowrap max-w-xs truncate" x-text="row[column] ?? '-'"></td>
                                                    </template>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </template>
                                <template x-if="!tableLoading && selectedTable && tableData.length === 0">
                                    <div class="text-center py-16 text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p>Table is empty</p>
                                    </div>
                                </template>
                                <template x-if="!tableLoading && !selectedTable">
                                    <div class="text-center py-16 text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                                        </svg>
                                        <p>Select a table to view its data</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Tab -->
            <div x-show="activeTab === 'actions'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <!-- Migrations -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Migrations</h3>
                        <p class="text-sm text-gray-400 mb-4">Run database migrations for the catalog module</p>
                        <div class="space-y-2">
                            <button @click="runMigrate()" 
                                    :disabled="actionRunning"
                                    class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-500 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg text-sm font-medium text-white transition-colors">
                                Run Migrate
                            </button>
                            <button @click="runMigrate('fresh')" 
                                    :disabled="actionRunning"
                                    class="w-full px-4 py-2 bg-red-600/80 hover:bg-red-500 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg text-sm font-medium text-white transition-colors">
                                Fresh (Drop All)
                            </button>
                        </div>
                    </div>

                    <!-- Seeders -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Seeders</h3>
                        <p class="text-sm text-gray-400 mb-4">Populate database with sample data</p>
                        <select x-model="selectedSeeder" class="w-full mb-2 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="">Select seeder...</option>
                            @foreach($seeders ?? [] as $seeder)
                                <option value="{{ $seeder }}">{{ class_basename($seeder) }}</option>
                            @endforeach
                        </select>
                        <button @click="runSeed()" 
                                :disabled="actionRunning || !selectedSeeder"
                                class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-500 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg text-sm font-medium text-white transition-colors">
                            Run Seeder
                        </button>
                    </div>

                    <!-- Cache -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-amber-500/10 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Clear Cache</h3>
                        <p class="text-sm text-gray-400 mb-4">Clear application cache and compiled files</p>
                        <button @click="clearCache()" 
                                :disabled="actionRunning"
                                class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-500 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg text-sm font-medium text-white transition-colors">
                            Clear All Cache
                        </button>
                    </div>
                </div>

                <!-- Action Output -->
                <div x-show="actionOutput" class="mt-6 bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                        <h3 class="font-medium text-white">Action Output</h3>
                        <button @click="actionOutput = ''" class="text-gray-400 hover:text-white text-sm">Clear</button>
                    </div>
                    <div class="p-6">
                        <pre class="bg-gray-900 rounded-xl p-4 text-sm font-mono text-gray-300 overflow-auto max-h-64 scrollbar-thin whitespace-pre-wrap" x-text="actionOutput"></pre>
                    </div>
                </div>
            </div>

            <!-- Info Tab -->
            <div x-show="activeTab === 'info'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Environment Info -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50">
                            <h3 class="font-medium text-white">Environment</h3>
                        </div>
                        <div class="divide-y divide-gray-700/50">
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">PHP Version</span>
                                <span class="text-white font-mono text-sm">{{ PHP_VERSION }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Laravel Version</span>
                                <span class="text-white font-mono text-sm">{{ app()->version() }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Environment</span>
                                <span class="text-white font-mono text-sm">{{ app()->environment() }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Debug Mode</span>
                                <span class="text-white font-mono text-sm">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Timezone</span>
                                <span class="text-white font-mono text-sm">{{ config('app.timezone') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Database Info -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50">
                            <h3 class="font-medium text-white">Database</h3>
                        </div>
                        <div class="divide-y divide-gray-700/50">
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Driver</span>
                                <span class="text-white font-mono text-sm">{{ config('database.default') }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Database</span>
                                <span class="text-white font-mono text-sm">{{ config('database.connections.' . config('database.default') . '.database') }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Host</span>
                                <span class="text-white font-mono text-sm">{{ config('database.connections.' . config('database.default') . '.host', 'N/A') }}</span>
                            </div>
                            <div class="px-6 py-3 flex justify-between">
                                <span class="text-gray-400">Catalog Tables</span>
                                <span class="text-white font-mono text-sm" x-text="tables.length"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Modules -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden md:col-span-2">
                        <div class="px-6 py-4 border-b border-gray-700/50">
                            <h3 class="font-medium text-white">Catalog Modules</h3>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 p-6">
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">Items</p>
                            </div>
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">Locations</p>
                            </div>
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-purple-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">Stock</p>
                            </div>
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">UoM</p>
                            </div>
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-pink-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">Taxonomy</p>
                            </div>
                            <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                                <div class="w-10 h-10 bg-cyan-500/10 rounded-lg flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-white">Pricing</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Notification Toast -->
        <div x-show="notification" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-6 right-6 z-50">
            <div :class="notification?.type === 'success' ? 'bg-emerald-600' : notification?.type === 'error' ? 'bg-red-600' : 'bg-blue-600'"
                 class="px-6 py-3 rounded-xl shadow-2xl text-white font-medium flex items-center gap-3">
                <svg x-show="notification?.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg x-show="notification?.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span x-text="notification?.message"></span>
            </div>
        </div>
    </div>

    <script>
        function adminPanel() {
            return {
                activeTab: 'tests',
                testsRunning: false,
                testFilter: '',
                testOutput: '',
                testResult: null,
                tables: @json($tables ?? []),
                selectedTable: null,
                tableData: [],
                tableColumns: [],
                tableLoading: false,
                actionRunning: false,
                actionOutput: '',
                selectedSeeder: '',
                notification: null,

                init() {
                    // Auto-load first table
                    if (this.tables.length > 0) {
                        // Don't auto-select to avoid initial load
                    }
                },

                async runTests() {
                    this.testsRunning = true;
                    this.testOutput = 'Initializing PHPUnit...\n';
                    this.testResult = null;

                    try {
                        const response = await fetch('/admin/tests', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ filter: this.testFilter })
                        });
                        const data = await response.json();
                        this.testOutput = data.output;
                        this.testResult = { success: data.success };
                        this.showNotification(data.success ? 'Tests completed successfully' : 'Some tests failed', data.success ? 'success' : 'error');
                    } catch (error) {
                        this.testOutput = 'Error: ' + error.message;
                        this.testResult = { success: false };
                        this.showNotification('Error running tests', 'error');
                    } finally {
                        this.testsRunning = false;
                    }
                },

                async selectTable(tableName) {
                    this.selectedTable = tableName;
                    this.tableLoading = true;
                    this.tableData = [];
                    this.tableColumns = [];

                    try {
                        const response = await fetch(`/admin/table/${tableName}`);
                        const data = await response.json();
                        this.tableData = data.data || [];
                        this.tableColumns = data.columns || [];
                    } catch (error) {
                        this.showNotification('Error loading table data', 'error');
                    } finally {
                        this.tableLoading = false;
                    }
                },

                async refreshTable() {
                    if (this.selectedTable) {
                        await this.selectTable(this.selectedTable);
                        this.showNotification('Table refreshed', 'success');
                    }
                },

                async runMigrate(type = 'run') {
                    this.actionRunning = true;
                    this.actionOutput = 'Running migrations...\n';

                    try {
                        const response = await fetch('/admin/migrate', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ type: type })
                        });
                        const data = await response.json();
                        this.actionOutput = data.output;
                        this.showNotification(data.success ? 'Migrations completed' : 'Migration failed', data.success ? 'success' : 'error');
                        
                        // Refresh tables list
                        this.refreshTablesList();
                    } catch (error) {
                        this.actionOutput = 'Error: ' + error.message;
                        this.showNotification('Error running migrations', 'error');
                    } finally {
                        this.actionRunning = false;
                    }
                },

                async runSeed() {
                    if (!this.selectedSeeder) return;
                    
                    this.actionRunning = true;
                    this.actionOutput = 'Running seeder...\n';

                    try {
                        const response = await fetch('/admin/seed', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ seeder: this.selectedSeeder })
                        });
                        const data = await response.json();
                        this.actionOutput = data.output;
                        this.showNotification(data.success ? 'Seeder completed' : 'Seeder failed', data.success ? 'success' : 'error');
                    } catch (error) {
                        this.actionOutput = 'Error: ' + error.message;
                        this.showNotification('Error running seeder', 'error');
                    } finally {
                        this.actionRunning = false;
                    }
                },

                async clearCache() {
                    this.actionRunning = true;
                    this.actionOutput = 'Clearing cache...\n';

                    try {
                        const response = await fetch('/admin/cache/clear', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });
                        const data = await response.json();
                        this.actionOutput = data.output;
                        this.showNotification('Cache cleared successfully', 'success');
                    } catch (error) {
                        this.actionOutput = 'Error: ' + error.message;
                        this.showNotification('Error clearing cache', 'error');
                    } finally {
                        this.actionRunning = false;
                    }
                },

                async refreshTablesList() {
                    try {
                        const response = await fetch('/admin/database');
                        const data = await response.json();
                        this.tables = data.tables || [];
                    } catch (error) {
                        console.error('Error refreshing tables:', error);
                    }
                },

                showNotification(message, type = 'info') {
                    this.notification = { message, type };
                    setTimeout(() => {
                        this.notification = null;
                    }, 3000);
                }
            }
        }
    </script>
</body>
</html>
