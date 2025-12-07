<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vessel - Configuración inicial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="min-h-full bg-slate-950 text-slate-100">
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">Vessel - Configuración inicial</h1>
            <p class="text-sm text-slate-400">Completa la conexión a base de datos y credenciales del panel admin.</p>
        </div>

        <div id="setup" class="space-y-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl" x-data="{ dbDriver: '{{ $db_driver }}' }">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-200">Database Driver</label>
                    <select name="db_driver" id="db_driver" class="w-full bg-gray-700/50 border border-gray-600 rounded px-3 py-2" x-model="dbDriver" required>
                        <option value="mysql" @selected($db_driver === 'mysql')>MySQL / MariaDB</option>
                        <option value="sqlite" @selected($db_driver === 'sqlite')>SQLite (file)</option>
                    </select>
                </div>

                <template x-if="dbDriver === 'mysql'">
                    <div class="space-y-2">
                        <label class="text-sm text-slate-300">DB Host</label>
                        <input id="db_host" type="text" value="{{ $db_host }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" placeholder="127.0.0.1" />
                    </div>
                </template>
                <template x-if="dbDriver === 'mysql'">
                    <div class="space-y-2">
                        <label class="text-sm text-slate-300">DB Puerto</label>
                        <input id="db_port" type="text" value="{{ $db_port }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" placeholder="3306" />
                    </div>
                </template>
                <template x-if="dbDriver === 'mysql'">
                    <div class="space-y-2">
                        <label class="text-sm text-slate-300">DB Nombre</label>
                        <input id="db_name" type="text" value="{{ $db_name }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" placeholder="vessel" />
                    </div>
                </template>
                <template x-if="dbDriver === 'mysql'">
                    <div class="space-y-2">
                        <label class="text-sm text-slate-300">DB Usuario</label>
                        <input id="db_user" type="text" value="{{ $db_user }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" placeholder="root" />
                    </div>
                </template>
                <template x-if="dbDriver === 'mysql'">
                    <div class="space-y-2">
                        <label class="text-sm text-slate-300">DB Password</label>
                        <input id="db_pass" type="password" value="{{ $db_pass }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" placeholder="(opcional)" />
                    </div>
                </template>

                <template x-if="dbDriver === 'sqlite'">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-200">SQLite Database Path</label>
                        <input id="db_path" type="text" value="{{ $db_path }}" class="w-full bg-gray-700/50 border border-gray-600 rounded px-3 py-2" placeholder="/full/path/to/database.sqlite" />
                        <p class="text-xs text-gray-400">Se crea el archivo si no existe.</p>
                    </div>
                </template>

                <div>
                    <label class="text-sm text-slate-300">APP URL</label>
                    <input id="app_url" type="text" value="{{ $app_url }}" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-300">Usuario admin</label>
                    <input id="admin_user" type="text" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-slate-300">Password admin</label>
                    <input id="admin_pass" type="password" class="mt-1 w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2" />
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="fresh" type="checkbox" class="h-4 w-4 text-blue-500 border-slate-700 rounded" checked />
                <label for="fresh" class="text-sm text-slate-300">Recrear base con migrate:fresh (recomendado en instalación limpia)</label>
            </div>

            <div id="error" class="hidden px-4 py-3 rounded-lg border-l-4 border-red-500 bg-red-500/10 text-red-100 text-sm shadow-lg"></div>
            <div id="success" class="hidden px-4 py-3 rounded-lg border-l-4 border-emerald-500 bg-emerald-500/10 text-emerald-100 text-sm shadow-lg"></div>

            <div class="flex justify-end">
                <button id="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold">Guardar y continuar</button>
            </div>
        </div>
    </div>

    <script>
        const btn = document.getElementById('submit');
        const errBox = document.getElementById('error');
        const okBox = document.getElementById('success');

        function showError(msg) {
            errBox.textContent = msg;
            errBox.classList.remove('hidden');
            okBox.classList.add('hidden');
        }

        function showSuccess(msg) {
            okBox.textContent = msg;
            okBox.classList.remove('hidden');
            errBox.classList.add('hidden');
        }

        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Guardando...';
            errBox.classList.add('hidden');
            okBox.classList.add('hidden');

            const driver = document.getElementById('db_driver').value;

            const payload = {
                db_driver: driver,
                db_host: driver === 'mysql' ? (document.getElementById('db_host')?.value || '') : '',
                db_port: driver === 'mysql' ? (document.getElementById('db_port')?.value || '') : '',
                db_name: driver === 'mysql' ? (document.getElementById('db_name')?.value || '') : '',
                db_user: driver === 'mysql' ? (document.getElementById('db_user')?.value || '') : '',
                db_pass: driver === 'mysql' ? (document.getElementById('db_pass')?.value || '') : '',
                db_path: driver === 'sqlite' ? (document.getElementById('db_path')?.value || '') : '',
                app_url: document.getElementById('app_url').value,
                admin_user: document.getElementById('admin_user').value,
                admin_pass: document.getElementById('admin_pass').value,
                fresh: document.getElementById('fresh').checked,
            };

            try {
                const res = await fetch('/setup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const raw = await res.text();
                let data = null;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    // Not JSON; show first part of raw response
                    throw new Error('Respuesta inesperada del servidor: ' + raw.slice(0, 200));
                }

                if (!res.ok || !data?.success) {
                    throw new Error(data?.error || 'No se pudo guardar');
                }

                showSuccess('✓ Configuración aplicada correctamente. Redirigiendo al panel admin...');
                setTimeout(() => window.location.href = '/admin', 2000);
            } catch (e) {
                showError(e.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Guardar y continuar';
            }
        });
    </script>
</body>
</html>
