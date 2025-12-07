<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vessel Admin - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-full bg-gradient-to-br from-slate-950 via-blue-950 to-slate-950 flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <div class="bg-slate-900/80 backdrop-blur-sm border border-slate-800 rounded-2xl p-8 shadow-2xl">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-white">Vessel Admin</h1>
                <p class="text-sm text-slate-400 mt-1">Ingresa tus credenciales</p>
            </div>

            <form id="loginForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Usuario</label>
                    <input type="text" name="username" id="username" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="admin" autocomplete="username">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Contraseña</label>
                    <input type="password" name="password" id="password" required
                        class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="••••••••" autocomplete="current-password">
                </div>

                <div id="error" class="hidden px-4 py-3 rounded-lg border-l-4 border-red-500 bg-red-500/10 text-red-100 text-sm"></div>

                <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                    Ingresar
                </button>
            </form>

            <p class="text-xs text-slate-500 text-center mt-6">
                Si no configuraste admin, ve a <a href="/setup" class="text-blue-400 hover:text-blue-300">/setup</a>
            </p>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const btn = document.getElementById('submitBtn');
        const errBox = document.getElementById('error');

        // Función para obtener token fresco
        async function refreshCsrfToken() {
            try {
                const res = await fetch('/setup', { method: 'HEAD' });
                const token = res.headers.get('X-CSRF-TOKEN') || 
                              document.querySelector('meta[name="csrf-token"]')?.content;
                if (token) {
                    document.querySelector('meta[name="csrf-token"]').content = token;
                }
                return token;
            } catch (e) {
                return document.querySelector('meta[name="csrf-token"]')?.content;
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            btn.textContent = 'Verificando...';
            errBox.classList.add('hidden');

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                let token = document.querySelector('meta[name="csrf-token"]')?.content;
                
                const res = await fetch('/admin/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({ username, password })
                });

                // Si es 419 (token expirado), refrescar y reintentar
                if (res.status === 419) {
                    token = await refreshCsrfToken();
                    const retry = await fetch('/admin/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token || ''
                        },
                        body: JSON.stringify({ username, password })
                    });
                    
                    if (!retry.ok) {
                        const data = await retry.json().catch(() => ({ error: 'Error de autenticación' }));
                        throw new Error(data.error || 'Credenciales incorrectas');
                    }
                    
                    window.location.href = '/admin';
                    return;
                }

                const data = await res.json().catch(() => ({ error: 'Error en respuesta del servidor' }));

                if (!res.ok || !data.success) {
                    throw new Error(data.error || 'Credenciales incorrectas');
                }

                window.location.href = '/admin';
            } catch (e) {
                errBox.textContent = e.message;
                errBox.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Ingresar';
            }
        });
    </script>
</body>
</html>
