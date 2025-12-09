<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vessel Admin Panel</title>
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
        <div x-data="adminPanel()" 
            x-init="tables = JSON.parse($el.dataset.tables)"
            data-tables="{{ json_encode($tables ?? []) }}"
            data-missing="{{ json_encode($missingTables ?? []) }}"
            data-needs-setup="{{ $needsAdminSetup ? 'true' : 'false' }}"
            data-config="{{ json_encode($configEntries ?? []) }}"
         x-cloak class="min-h-full flex flex-col">
        
        <!-- Header -->
        <header class="glass-effect border-b border-gray-700/50 sticky top-0 z-50">
            <div class="max-w-screen-2xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center font-bold text-lg shadow-lg shadow-blue-500/20">
                            V
                        </div>
                        <div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                                Vessel Admin Panel
                            </h1>
                            <p class="text-xs text-gray-400">Debug & Testing Console</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 text-xs font-medium rounded-full border border-emerald-500/20">
                            {{ app()->environment() }}
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
                    <button @click="activeTab = 'logs'; if (!logsLoaded) { loadLogs(); }" 
                            :class="activeTab === 'logs' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h9m-5 8v1a2 2 0 01-2 2H6l-3 3V6a2 2 0 012-2h7" />
                        </svg>
                        Logs
                    </button>
                    <button @click="activeTab = 'sql'" 
                            :class="activeTab === 'sql' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c-1.333 0-2 .667-2 2s.667 2 2 2 2-.667 2-2-.667-2-2-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v2" />
                        </svg>
                        SQL
                    </button>
                    <button @click="activeTab = 'routes'; if (!routesLoaded) { loadRoutes(); }" 
                            :class="activeTab === 'routes' ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800'"
                            class="px-5 py-3 text-sm font-medium rounded-t-lg transition-all duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m-6 4h18m-9-2v2m-9 4h12m-6-2v2" />
                        </svg>
                        Routes
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
            <template x-if="missing.length > 0">
                <div class="mb-6 bg-amber-500/10 border border-amber-500/40 text-amber-100 rounded-2xl p-5 flex flex-col gap-3">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-lg font-semibold">Migraciones pendientes</h2>
                        <p class="text-sm text-amber-200/90">Faltan tablas básicas: <span class="font-mono" x-text="missing.join(', ')"></span>. Ejecuta migraciones para inicializar Vessel (Portal + shared_config).</p>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        <button @click="runMigrate()" class="px-4 py-2 rounded-lg bg-amber-500 text-amber-950 font-semibold shadow">Ejecutar migraciones</button>
                    </div>
                </div>
            </template>

            <template x-if="missing.length === 0 && needsAdminSetup">
                <div class="mb-6 bg-amber-500/10 border border-amber-500/40 text-amber-100 rounded-2xl p-5 flex flex-col gap-3">
                    <div class="flex flex-col gap-1">
                        <h2 class="text-lg font-semibold">Protege el panel</h2>
                        <p class="text-sm text-amber-200/90">Define credenciales admin para habilitar el Basic Auth.</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                        <input x-model="adminUser" type="text" placeholder="Usuario admin" class="bg-gray-900 border border-amber-500/50 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:border-transparent" />
                        <input x-model="adminPass" type="password" placeholder="Password" class="bg-gray-900 border border-amber-500/50 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-amber-400 focus:border-transparent" />
                        <button @click="saveAdminCredentials()" class="px-4 py-2 rounded-lg bg-emerald-500 text-emerald-950 font-semibold shadow">Guardar credenciales</button>
                    </div>
                </div>
            </template>
            
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
                                <div class="flex flex-col gap-2 mt-2">
                                    <p class="text-xs text-gray-400" x-text="tables.length + ' tables'"></p>
                                    <input x-model="tableSearch" type="text" placeholder="Filtrar tablas..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="max-h-[calc(100vh-280px)] overflow-y-auto scrollbar-thin">
                                <template x-for="table in tables.filter(t => !tableSearch || t.name.toLowerCase().includes(tableSearch.toLowerCase()))" :key="table.name">
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

            <!-- Logs Tab -->
            <div x-show="activeTab === 'logs'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="space-y-4">
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6 flex flex-col gap-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-semibold text-white">Laravel Logs</h2>
                            <span class="text-xs text-gray-400">Solo lectura • Entorno dev</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <input x-model="logSearch" type="text" placeholder="Buscar texto..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <select x-model="logLevel" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Todos los niveles</option>
                                <option value="error">Error</option>
                                <option value="warning">Warning</option>
                                <option value="info">Info</option>
                                <option value="debug">Debug</option>
                            </select>
                            <input x-model.number="logLimit" type="number" min="10" max="500" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Limite de lineas">
                            <div class="flex gap-2 justify-end">
                                <button @click="loadLogs()" :disabled="logLoading" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 disabled:bg-gray-600 disabled:cursor-not-allowed flex items-center gap-2">
                                    <svg x-show="!logLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <svg x-show="logLoading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="logLoading ? 'Cargando...' : 'Actualizar'"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <h3 class="font-medium text-white">Entradas recientes</h3>
                            <span class="text-xs text-gray-400" x-text="logEntries.length + ' mostradas'"></span>
                        </div>
                        <div class="max-h-[calc(100vh-320px)] overflow-y-auto scrollbar-thin divide-y divide-gray-800/80">
                            <template x-if="logEntries.length === 0 && !logLoading">
                                <div class="py-12 text-center text-gray-500">Sin registros para los filtros actuales</div>
                            </template>
                            <template x-if="logLoading">
                                <div class="py-12 text-center text-gray-400 flex items-center justify-center gap-3">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Cargando...
                                </div>
                            </template>
                            <template x-for="(entry, idx) in logEntries" :key="idx">
                                <div class="px-6 py-4 flex flex-col gap-1">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-gray-500" x-text="entry.timestamp || '-' "></span>
                                        <span class="text-xs text-gray-500" x-text="entry.env || 'local'"></span>
                                        <span :class="{
                                            'bg-red-500/10 text-red-300 border border-red-500/30': entry.level === 'error',
                                            'bg-amber-500/10 text-amber-300 border border-amber-500/30': entry.level === 'warning',
                                            'bg-blue-500/10 text-blue-300 border border-blue-500/30': entry.level === 'info',
                                            'bg-gray-500/10 text-gray-300 border border-gray-500/30': !['error','warning','info'].includes(entry.level)
                                        }" class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wide">
                                            <span x-text="entry.level || 'info'"></span>
                                        </span>
                                    </div>
                                    <pre class="whitespace-pre-wrap text-sm text-gray-200 font-mono leading-relaxed bg-gray-900/40 rounded-lg px-3 py-2 border border-gray-800" x-text="entry.message"></pre>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SQL Tab -->
            <div x-show="activeTab === 'sql'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6 flex flex-col gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Ejecutar SELECT</h2>
                            <p class="text-xs text-gray-400 mt-1">Solo tablas de Catálogo, lectura y limite automático</p>
                        </div>
                        <textarea x-model="sqlQuery" rows="8" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="SELECT * FROM catalog_items LIMIT 20;"></textarea>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Solo SELECT | Se forza LIMIT 100 si no está presente</span>
                        </div>
                        <button @click="runSqlQuery()" :disabled="sqlRunning || !sqlQuery.trim()" class="w-full px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 disabled:bg-gray-600 disabled:cursor-not-allowed flex items-center gap-2 justify-center">
                            <svg x-show="!sqlRunning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            </svg>
                            <svg x-show="sqlRunning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="sqlRunning ? 'Ejecutando...' : 'Ejecutar'"></span>
                        </button>
                        <p class="text-xs text-gray-500">Tip: usa JOIN pero solo con tablas que comiencen con catalog_, stock_, locations_, uom_, taxonomy_, pricing_ o portal.</p>
                    </div>

                    <div class="lg:col-span-2 bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-white">Resultado</h3>
                                <p class="text-xs text-gray-400" x-show="sqlResult" x-text="(sqlResult?.count || 0) + ' filas · ' + (sqlResult?.duration_ms || 0) + ' ms'"></p>
                            </div>
                            <button @click="sqlResult = null; sqlError = ''" class="text-xs text-gray-400 hover:text-white">Limpiar</button>
                        </div>
                        <div class="p-6">
                            <template x-if="sqlError">
                                <div class="mb-4 px-4 py-3 rounded-lg border border-red-500/40 bg-red-500/10 text-red-200 text-sm" x-text="sqlError"></div>
                            </template>
                            <template x-if="sqlRunning">
                                <div class="py-10 text-center text-gray-400 flex items-center justify-center gap-3">
                                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Ejecutando consulta...
                                </div>
                            </template>
                            <template x-if="sqlResult && sqlResult.rows && sqlResult.rows.length">
                                <div class="overflow-auto max-h-[calc(100vh-340px)] scrollbar-thin">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-900/50 sticky top-0">
                                            <tr>
                                                <template x-for="column in sqlResult.columns" :key="column">
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap" x-text="column"></th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-700/50">
                                            <template x-for="(row, idx) in sqlResult.rows" :key="idx">
                                                <tr class="hover:bg-gray-700/30 transition-colors">
                                                    <template x-for="column in sqlResult.columns" :key="column">
                                                        <td class="px-4 py-3 text-gray-300 whitespace-nowrap max-w-xs truncate" x-text="row[column] ?? '-' "></td>
                                                    </template>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                            <template x-if="sqlResult && (!sqlResult.rows || sqlResult.rows.length === 0) && !sqlRunning && !sqlError">
                                <div class="py-10 text-center text-gray-500">Sin resultados</div>
                            </template>
                            <template x-if="!sqlResult && !sqlRunning && !sqlError">
                                <div class="py-10 text-center text-gray-500">Ingresa una consulta SELECT y ejecuta.</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Routes Tab -->
            <div x-show="activeTab === 'routes'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="space-y-6">
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="text-xs text-gray-400">Buscar</label>
                            <input x-model="routeSearch" @input.debounce.400ms="loadRoutes" type="text" placeholder="URI, name o action" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-400">Método</label>
                            <select x-model="routeMethod" @change="loadRoutes" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Todos</option>
                                <option>GET</option>
                                <option>POST</option>
                                <option>PUT</option>
                                <option>PATCH</option>
                                <option>DELETE</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-400">Módulo</label>
                            <select x-model="routeModule" @change="loadRoutes" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Todos</option>
                                <template x-for="mod in modules" :key="mod.name">
                                    <option :value="mod.name" x-text="mod.name"></option>
                                </template>
                            </select>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button @click="loadRoutes" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Actualizar
                            </button>
                        </div>
                    </div>

                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-white">Rutas</h3>
                                <p class="text-xs text-gray-400" x-text="routes.length + ' rutas'" ></p>
                            </div>
                            <div class="text-xs text-gray-400">Equivalente a route:list con filtros por módulo</div>
                        </div>
                        <div class="overflow-auto max-h-[calc(100vh-320px)] scrollbar-thin">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-900/50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Módulo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Método</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">URI</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Name</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    <template x-for="(r, idx) in routes" :key="idx">
                                        <tr class="hover:bg-gray-700/30">
                                            <td class="px-4 py-3 text-gray-300 font-mono text-xs" x-text="r.module"></td>
                                            <td class="px-4 py-3 text-gray-200 font-mono text-xs" x-text="r.methods.join(',')"></td>
                                            <td class="px-4 py-3 text-gray-100 font-mono text-xs" x-text="'/' + r.uri"></td>
                                            <td class="px-4 py-3 text-gray-400 font-mono text-xs" x-text="r.name || '-' "></td>
                                            <td class="px-4 py-3 text-gray-400 font-mono text-[11px]" x-text="r.action"></td>
                                        </tr>
                                    </template>
                                    <template x-if="routes.length === 0">
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-gray-500 text-sm">Sin rutas para los filtros actuales</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
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

                    <!-- Auto Update -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 8v4m4-12h2a2 2 0 012 2v4a2 2 0 01-2 2h-2m-8-8H6a2 2 0 00-2 2v4a2 2 0 002 2h2" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12a4 4 0 118 0 4 4 0 01-8 0z" />
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">Auto Update</h3>
                        <p class="text-sm text-gray-400 mb-4">Pull de git + composer + migrate (dev only, APP_ALLOW_UPDATE)</p>
                        <input x-model="updateBranch" type="text" placeholder="branch (default main)" class="w-full mb-2 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        <button @click="runUpdate()" 
                                :disabled="actionRunning"
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-500 disabled:bg-gray-600 disabled:cursor-not-allowed rounded-lg text-sm font-medium text-white transition-colors flex items-center justify-center gap-2">
                            <svg x-show="!actionRunning" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <svg x-show="actionRunning" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="actionRunning ? 'Ejecutando...' : 'Actualizar' "></span>
                        </button>
                        <p class="text-[11px] text-gray-500 mt-2">Necesita git y APP_ALLOW_UPDATE=true. Usa branch opcional.</p>
                    </div>

                    <!-- Access Tokens -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-6 md:col-span-2 lg:col-span-3">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-indigo-500/10 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 1.105-.895 2-2 2s-2-.895-2-2 .895-2 2-2 2 .895 2 2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7 6.97 6.97 0 01-3-.677L5 19l1.677-4a7 7 0 1112.323-4z" />
                                </svg>
                            </div>
                            <button @click="fetchAccessTokens()" class="text-xs px-3 py-1 rounded-md border border-gray-700 bg-gray-900 hover:bg-gray-800 text-gray-200">Refrescar</button>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">API Access Tokens</h3>
                        <p class="text-sm text-gray-400 mb-4">Genera tokens para usar con el header VESSEL-ACCESS-PRIVATE.</p>

                        <div class="grid md:grid-cols-3 gap-3 mb-3">
                            <div class="space-y-1">
                                <label class="text-xs text-gray-400">Nombre (opcional)</label>
                                <input x-model="tokenName" type="text" placeholder="Dashboard / Scripts" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-gray-400">Scope</label>
                                <select x-model="tokenScope" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="all">all (full access)</option>
                                    <option value="own">own (scoped)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs text-gray-400">Workspace ID (opcional)</label>
                                <input x-model="tokenWorkspaceId" type="text" placeholder="workspace-123" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <button @click="createAccessToken()"
                                    :disabled="tokenCreating"
                                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-600 disabled:cursor-not-allowed text-white text-sm font-medium flex items-center gap-2">
                                <svg x-show="tokenCreating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="tokenCreating ? 'Creando...' : 'Crear token'"></span>
                            </button>
                            <template x-if="newTokenValue">
                                <div class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-xs font-mono text-gray-100 break-all" x-text="newTokenValue"></div>
                            </template>
                        </div>

                        <div class="mt-4 border border-gray-700/60 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 border-b border-gray-700/60 flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-white">Tokens existentes</h4>
                                <span class="text-xs text-gray-400" x-text="accessTokens.length + ' tokens'"></span>
                            </div>
                            <div class="overflow-auto max-h-72">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-900/50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Nombre</th>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Scope</th>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Workspace</th>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Creado</th>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Token</th>
                                            <th class="px-3 py-2 text-left text-xs text-gray-400">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        <template x-if="!accessTokens.length">
                                            <tr>
                                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">No hay tokens creados.</td>
                                            </tr>
                                        </template>
                                        <template x-for="token in accessTokens" :key="token.id">
                                            <tr class="hover:bg-gray-700/30">
                                                <td class="px-3 py-2 text-gray-100 font-mono text-xs" x-text="token.name || '-' "></td>
                                                <td class="px-3 py-2"><span class="px-2 py-1 text-[11px] rounded-md" :class="token.scope === 'all' ? 'bg-indigo-500/20 text-indigo-200' : 'bg-amber-500/20 text-amber-200'" x-text="token.scope"></span></td>
                                                <td class="px-3 py-2 text-gray-300 font-mono text-xs" x-text="token.workspace_id || '-' "></td>
                                                <td class="px-3 py-2 text-gray-400 text-xs" x-text="token.created_at ? new Date(token.created_at).toLocaleString() : '-' "></td>
                                                <td class="px-3 py-2 text-gray-100 font-mono text-[11px] break-all" x-text="token.token"></td>
                                                <td class="px-3 py-2">
                                                    <button @click="revokeToken(token.id)" class="text-xs text-red-300 hover:text-red-100">Revocar</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
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

                    <!-- Config Store -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <h3 class="font-medium text-white">Config (shared_config)</h3>
                            <button @click="loadConfig()" class="text-xs px-3 py-1 rounded-md border border-gray-700 bg-gray-900 hover:bg-gray-800 text-gray-200">Refrescar</button>
                        </div>
                        <div class="p-6 space-y-3">
                            <div class="space-y-2">
                                <label class="text-xs text-gray-400">Clave</label>
                                <input x-model="configKey" type="text" placeholder="ej: modules.catalog.enabled" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                <label class="text-xs text-gray-400">Valor (se almacena como texto/JSON)</label>
                                <textarea x-model="configValue" rows="2" placeholder='ej: true o {"foo":"bar"}' class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                <div class="flex gap-2">
                                    <button @click="saveConfig()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm">Guardar</button>
                                    <button @click="deleteConfig()" class="px-4 py-2 rounded-lg bg-red-600/70 hover:bg-red-500 text-white text-sm">Eliminar</button>
                                </div>
                            </div>
                            <div class="max-h-60 overflow-auto border border-gray-700/60 rounded-lg divide-y divide-gray-800">
                                <template x-if="!configEntries.length">
                                    <p class="text-sm text-gray-500 p-3">Sin entradas.</p>
                                </template>
                                <template x-for="item in configEntries" :key="item.key">
                                    <div class="px-3 py-2 flex flex-col gap-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-mono text-xs text-gray-300" x-text="item.key"></span>
                                            <button @click="configKey = item.key; configValue = typeof item.value === 'string' ? item.value : JSON.stringify(item.value, null, 2)" class="text-[11px] text-blue-300 hover:text-blue-100">Editar</button>
                                        </div>
                                        <pre class="text-xs text-gray-400 bg-gray-900/60 rounded px-2 py-1 whitespace-pre-wrap" x-text="JSON.stringify(item.value, null, 2)"></pre>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Modules -->
                    <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 overflow-hidden md:col-span-2">
                        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
                            <h3 class="font-medium text-white">Módulos instalados</h3>
                            <span class="text-xs text-gray-400" x-text="modules?.length + ' módulos'"></span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-6">
                            <template x-for="mod in modules" :key="mod.name">
                                <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-800">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-semibold text-white" x-text="mod.name"></span>
                                        <span :class="mod.enabled ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/15 text-red-300 border border-red-500/30'" class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wide">
                                            <span x-text="mod.enabled ? 'on' : 'off'"></span>
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-400">
                                        <span>WebSockets</span>
                                        <span :class="mod.ws_enabled ? 'text-emerald-300' : 'text-gray-500'" class="font-mono" x-text="mod.ws_enabled ? 'enabled' : 'disabled'"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                                        <span>Migraciones</span>
                                        <span :class="(mod.pending_migrations ?? 0) > 0 ? 'text-amber-300' : 'text-emerald-300'" class="font-mono" x-text="(mod.pending_migrations ?? 'n/a')"></span>
                                    </div>
                                    <div class="mt-2 text-[11px] text-gray-500 break-all" x-text="mod.provider || 'provider?'" title="Proveedor"></div>
                                    <div class="text-[11px] text-gray-500" :class="mod.loaded ? 'text-emerald-300' : 'text-gray-500'">Provider: <span x-text="mod.loaded ? 'loaded' : 'not loaded'"></span></div>
                                    <div class="mt-3 flex gap-2">
                                        <button @click="toggleModule(mod.name, !mod.enabled)" class="flex-1 text-xs px-3 py-1.5 rounded-md border border-gray-700 bg-gray-800 hover:bg-gray-700 text-gray-200">{{ __('Toggle') }}</button>
                                        <button @click="toggleModule(mod.name, mod.enabled, !mod.ws_enabled)" class="flex-1 text-xs px-3 py-1.5 rounded-md border border-gray-700 bg-gray-800 hover:bg-gray-700 text-gray-200">WS</button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!modules || modules.length === 0">
                                <div class="col-span-full text-center text-gray-500 text-sm py-8">Sin módulos registrados</div>
                            </template>
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
                tables: [],
                tableSearch: '',
                selectedTable: null,
                tableData: [],
                tableColumns: [],
                tableLoading: false,
                actionRunning: false,
                actionOutput: '',
                selectedSeeder: '',
                notification: null,
                updateBranch: '',
                logsLoaded: false,
                logEntries: [],
                logLevel: '',
                logSearch: '',
                logLimit: 200,
                logLoading: false,
                sqlQuery: 'SELECT * FROM catalog_items LIMIT 20;',
                sqlResult: null,
                sqlRunning: false,
                sqlError: '',
                modules: <?php echo json_encode($modules ?? []); ?>,
                missing: [],
                needsAdminSetup: false,
                routesLoaded: false,
                routes: [],
                routeSearch: '',
                routeMethod: '',
                routeModule: '',
                adminUser: '',
                adminPass: '',
                configEntries: [],
                configKey: '',
                configValue: '',
                accessTokens: [],
                tokenName: '',
                tokenScope: 'all',
                tokenWorkspaceId: '',
                newTokenValue: '',
                tokenCreating: false,

                async parseJsonResponse(response) {
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Respuesta inesperada del servidor: ' + text.slice(0, 200));
                    }
                },

                init() {
                    // Load datasets from root element
                    this.tables = JSON.parse(this.$root.dataset.tables || '[]');
                    this.missing = JSON.parse(this.$root.dataset.missing || '[]');
                    this.needsAdminSetup = (this.$root.dataset.needsSetup || 'false') === 'true';
                    this.configEntries = JSON.parse(this.$root.dataset.config || '[]');
                    this.fetchAccessTokens();
                },

                async fetchAccessTokens() {
                    try {
                        const response = await fetch('/admin/tokens');
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) {
                            throw new Error(data.error || 'No se pudo cargar tokens');
                        }
                        this.accessTokens = data.tokens || [];
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async createAccessToken() {
                    this.tokenCreating = true;
                    this.newTokenValue = '';

                    try {
                        const response = await fetch('/admin/tokens', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                name: this.tokenName || null,
                                scope: this.tokenScope,
                                workspace_id: this.tokenWorkspaceId || null,
                            })
                        });

                        const data = await this.parseJsonResponse(response);
                        if (!data.success) {
                            throw new Error(data.error || 'No se pudo crear el token');
                        }

                        this.newTokenValue = data.token?.token || '';
                        this.tokenName = '';
                        this.tokenWorkspaceId = '';
                        await this.fetchAccessTokens();
                        this.showNotification('Token creado', 'success');
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    } finally {
                        this.tokenCreating = false;
                    }
                },

                async revokeToken(id) {
                    if (!id) return;

                    try {
                        const response = await fetch(`/admin/tokens/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await this.parseJsonResponse(response);
                        if (!response.ok || !data.success) {
                            throw new Error(data.error || 'No se pudo revocar el token');
                        }

                        await this.fetchAccessTokens();
                        this.showNotification('Token revocado', 'success');
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async runTests() {
                    this.testsRunning = true;
                    this.testOutput = 'Initializing PHPUnit...\n';
                    this.testResult = null;

                    try {
                        const response = await fetch('/admin/tests/run', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ filter: this.testFilter })
                        });
                        const data = await this.parseJsonResponse(response);
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
                        const response = await fetch(`/admin/database/table/${tableName}`);
                        const data = await this.parseJsonResponse(response);
                        this.tableData = data.data || [];
                        this.tableColumns = data.columns || [];
                    } catch (error) {
                        this.showNotification('Error loading table data', 'error');
                    } finally {
                        this.tableLoading = false;
                    }
                },

                async loadConfig() {
                    try {
                        const response = await fetch('/admin/config');
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) throw new Error(data.error || 'No se pudo cargar config');
                        this.configEntries = data.entries || [];
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async saveConfig() {
                    if (!this.configKey.trim()) {
                        this.showNotification('Clave requerida', 'error');
                        return;
                    }
                    let payloadValue = this.configValue;
                    try {
                        // Try to parse JSON if looks like object/array/true/false/null/number
                        const trimmed = (this.configValue || '').trim();
                        if (['{', '[', '"', 't', 'f', 'n', '-', '0','1','2','3','4','5','6','7','8','9'].includes(trimmed[0])) {
                            payloadValue = JSON.parse(trimmed);
                        }
                    } catch (e) {
                        // keep as string
                        payloadValue = this.configValue;
                    }

                    try {
                        const response = await fetch('/admin/config', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ key: this.configKey, value: payloadValue })
                        });
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) throw new Error(data.error || 'No se pudo guardar');
                        this.configEntries = data.entries || [];
                        this.showNotification('Config guardada', 'success');
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async deleteConfig() {
                    if (!this.configKey.trim()) {
                        this.showNotification('Clave requerida para eliminar', 'error');
                        return;
                    }

                    try {
                        const response = await fetch('/admin/config', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ key: this.configKey })
                        });
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) throw new Error(data.error || 'No se pudo eliminar');
                        this.configEntries = data.entries || [];
                        this.showNotification('Config eliminada', 'success');
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async refreshTable() {
                    if (this.selectedTable) {
                        await this.selectTable(this.selectedTable);
                        this.showNotification('Table refreshed', 'success');
                    }
                },

                async loadLogs() {
                    this.logLoading = true;
                    this.logsLoaded = true;

                    try {
                        const params = new URLSearchParams({
                            level: this.logLevel || '',
                            search: this.logSearch || '',
                            limit: this.logLimit || 200,
                        });

                        const response = await fetch(`/admin/logs?${params.toString()}`);
                        const data = await this.parseJsonResponse(response);
                        this.logEntries = data.entries || [];
                    } catch (error) {
                        this.showNotification('Error cargando logs', 'error');
                    } finally {
                        this.logLoading = false;
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
                            body: JSON.stringify({ action: type })
                        });
                        const data = await this.parseJsonResponse(response);
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

                async toggleModule(module, enabled, wsEnabled = null) {
                    try {
                        const response = await fetch('/admin/modules/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ module, enabled, ws_enabled: wsEnabled })
                        });

                        const data = await this.parseJsonResponse(response);

                        if (!response.ok || !data.success) {
                            throw new Error(data.error || 'No se pudo actualizar el módulo');
                        }

                        this.modules = data.modules || this.modules;
                        this.showNotification('Módulo actualizado', 'success');
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                async loadRoutes() {
                    this.routesLoaded = true;
                    try {
                        const params = new URLSearchParams({
                            search: this.routeSearch || '',
                            method: this.routeMethod || '',
                            module: this.routeModule || '',
                        });
                        const response = await fetch(`/admin/routes?${params.toString()}`);
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) {
                            throw new Error(data.error || 'No se pudo cargar rutas');
                        }
                        this.routes = data.routes || [];
                    } catch (error) {
                        this.showNotification(error.message, 'error');
                    }
                },

                needsSetup() {
                    return this.needsAdminSetup || (Array.isArray(this.missing) && this.missing.length > 0);
                },

                async saveAdminCredentials() {
                    try {
                        const user = this.adminUser?.trim();
                        const password = this.adminPass || '';
                        if (!user || !password) {
                            throw new Error('Usuario y password requeridos');
                        }

                        const response = await fetch('/admin/setup/credentials', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ user, password })
                        });
                        const data = await this.parseJsonResponse(response);
                        if (!data.success) {
                            throw new Error(data.error || 'No se pudo guardar');
                        }
                        this.showNotification('Credenciales guardadas. Reingresa con Basic Auth.', 'success');
                        this.needsAdminSetup = false;
                    } catch (error) {
                        this.showNotification(error.message, 'error');
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
                        const data = await this.parseJsonResponse(response);
                        this.actionOutput = data.output;
                        this.showNotification(data.success ? 'Seeder completed' : 'Seeder failed', data.success ? 'success' : 'error');
                    } catch (error) {
                        this.actionOutput = 'Error: ' + error.message;
                        this.showNotification('Error running seeder', 'error');
                    } finally {
                        this.actionRunning = false;
                    }
                },

                async runUpdate() {
                    this.actionRunning = true;
                    this.actionOutput = 'Checking for updates...\n';

                    try {
                        const response = await fetch('/admin/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ branch: this.updateBranch || null })
                        });
                        const data = await this.parseJsonResponse(response);

                        if (!data.success) {
                            this.actionOutput = data.error || 'Update failed';
                            this.showNotification('Update failed', 'error');
                            return;
                        }

                        this.actionOutput = data.output || 'Update completed';
                        this.showNotification('Update completed', 'success');
                    } catch (error) {
                        this.actionOutput = 'Error: ' + error.message;
                        this.showNotification('Error executing update', 'error');
                    } finally {
                        this.actionRunning = false;
                    }
                },

                async runSqlQuery() {
                    if (!this.sqlQuery.trim()) return;

                    this.sqlRunning = true;
                    this.sqlError = '';
                    this.sqlResult = null;

                    try {
                        const response = await fetch('/admin/sql', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ query: this.sqlQuery })
                        });

                        const data = await this.parseJsonResponse(response);

                        if (!data.success) {
                            this.sqlError = data.error || 'Error ejecutando consulta';
                            return;
                        }

                        this.sqlResult = data;
                    } catch (error) {
                        this.sqlError = error.message || 'Error ejecutando consulta';
                    } finally {
                        this.sqlRunning = false;
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
                        const data = await this.parseJsonResponse(response);
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
                        const data = await this.parseJsonResponse(response);
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
