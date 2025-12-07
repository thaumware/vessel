<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $appName }} | Vessel</title>
    <style>
        body { margin: 0; font-family: "Inter", system-ui, -apple-system, sans-serif; background: radial-gradient(circle at 20% 20%, #0ea5e9 0, transparent 35%), radial-gradient(circle at 80% 10%, #a855f7 0, transparent 30%), #0b1220; color: #e5e7eb; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .card { background: rgba(17, 24, 39, 0.75); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 18px; padding: 32px; max-width: 520px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.35); backdrop-filter: blur(8px); }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: rgba(14,165,233,0.15); color: #38bdf8; font-size: 12px; font-weight: 600; letter-spacing: 0.02em; }
        .title { margin: 18px 0 8px; font-size: 28px; font-weight: 700; letter-spacing: -0.02em; }
        .subtitle { margin: 0 0 18px; color: #cbd5e1; line-height: 1.5; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 18px 0; }
        .pill { padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); }
        .pill .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 6px; }
        .pill .value { font-size: 14px; font-weight: 600; color: #e5e7eb; }
        .actions { display: flex; gap: 12px; margin-top: 20px; flex-wrap: wrap; }
        .btn { display: inline-flex; justify-content: center; align-items: center; gap: 8px; padding: 12px 16px; border-radius: 12px; text-decoration: none; font-weight: 700; transition: transform 160ms ease, box-shadow 160ms ease, background 160ms ease; }
        .btn.primary { background: linear-gradient(135deg, #0ea5e9, #6366f1); color: #0b1220; box-shadow: 0 12px 30px rgba(99,102,241,0.35); }
        .btn.secondary { background: rgba(255,255,255,0.06); color: #e5e7eb; border: 1px solid rgba(255,255,255,0.08); }
        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); box-shadow: none; }
        footer { margin-top: 18px; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">Vessel · API ready</div>
        <div class="title">{{ $appName }}</div>
        <p class="subtitle">Servicio Vessel en ejecuci&oacute;n. Usa el bot&oacute;n para abrir el panel admin o consulta el endpoint de estado.</p>
        <div class="meta">
            <div class="pill">
                <div class="label">Versi&oacute;n</div>
                <div class="value">{{ $version }}</div>
            </div>
            <div class="pill">
                <div class="label">Entorno</div>
                <div class="value">{{ $env }}</div>
            </div>
            <div class="pill">
                <div class="label">Hora del servidor</div>
                <div class="value">{{ $timestamp }}</div>
            </div>
            <div class="pill">
                <div class="label">API status</div>
                <div class="value">{{ $status }}</div>
            </div>
        </div>
        <div class="actions">
            <a class="btn primary" href="{{ url('/admin') }}">Ir al admin</a>
            <a class="btn secondary" href="{{ url('/api/status') }}">Ver estado API</a>
        </div>
        <footer>Ruta / responde con esta landing. El estado JSON est&aacute; en /api/status.</footer>
    </div>
</body>
</html>
