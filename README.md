# vessel
# vessel
php artisan serve 

Monorepo for the Vessel project. It contains two main parts:

- `backend/` — Laravel (PHP) backend
- `frontend/` — React + Vite frontend

This README explains how to install dependencies and run both parts locally on Windows/PowerShell.

Prerequisites
 - PHP 8.1+ (for Laravel)
 - Composer
 - Node.js 18+ and npm
 - SQLite (optional — the project includes a sqlite file in `backend/database/`)

Run the backend (Laravel)

Open a PowerShell terminal and run from the repo root:

```powershell
cd backend
composer install
cp .env.example .env
# If you want to use the included SQLite DB, ensure the DB file exists:
# (a database.sqlite file is already included under backend/database/)
php artisan key:generate
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8000
```

The backend will be available at http://127.0.0.1:8000 by default.

Run the frontend (React + Vite)

Open a second PowerShell terminal and run from the repo root:

```powershell
cd frontend
npm install
npm run dev
```

This will start the Vite dev server (HMR). By default Vite prints the local URL in the terminal (usually http://localhost:5173). If you prefer a production build:

```powershell
npm run build:tsc    # runs tsc then vite build (if configured)
npm run build        # vite build only
```

Troubleshooting
- If `npm install` fails with peer dependency issues, try:
	```powershell
	npm install --legacy-peer-deps
	```
- If Vite fails to load because a different Vite config expects another node_modules, the repo contains a helper config at `frontend/vite.design.config.ts` (used to point Vite at the design root). In most cases the local `frontend` config is the one to use.
- If TypeScript errors appear related to temporary `any` casts introduced during a refactor, they are safe for local dev but should be fixed before pushing to production.

If you want me to prepare a single script to run both servers concurrently or to create a dev `Makefile`/PowerShell script, tell me and I'll add it.