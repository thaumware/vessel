<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900">
    <div class="min-h-full flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center font-bold text-2xl text-white shadow-lg shadow-blue-500/20 mx-auto">
                    C
                </div>
                <h2 class="mt-6 text-3xl font-bold text-white">
                    Admin Panel
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    Catalog Debug & Testing Console
                </p>
            </div>

            <!-- Login Form -->
            <div class="bg-gray-800/50 rounded-2xl border border-gray-700/50 p-8 backdrop-blur-sm">
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.authenticate') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                            Usuario
                        </label>
                        <input type="text" 
                               id="username" 
                               name="username" 
                               required 
                               autocomplete="username"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                               placeholder="admin">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Password
                        </label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                               placeholder="********">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember" 
                               class="w-4 h-4 bg-gray-900 border-gray-700 rounded text-blue-600 focus:ring-blue-500">
                        <label for="remember" class="ml-2 text-sm text-gray-400">
                            Recordar sesion
                        </label>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white font-medium rounded-xl shadow-lg shadow-blue-500/25 transition-all duration-200 transform hover:scale-[1.02]">
                        Iniciar Sesion
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-700/50">
                    <p class="text-xs text-gray-500 text-center">
                        Credenciales por defecto en desarrollo:<br>
                        <code class="text-gray-400">admin / admin123</code>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-600">
                Configura ADMIN_USERNAME y ADMIN_PASSWORD en .env para produccion
            </p>
        </div>
    </div>
</body>
</html>
