# Frontend — React + TypeScript + Vite

This folder contains the React frontend built with Vite and TypeScript. The instructions below use PowerShell on Windows (adjust for other shells).

## Prerequisites
- Node.js 18+ and npm
- Git (optional)

## Install dependencies

Open PowerShell in `f:/Vessel/frontend` (or from repo root):

```powershell
cd f:/Vessel/frontend
npm install
```

If you run into peer dependency resolution issues, try:

```powershell
npm install --legacy-peer-deps
```

## Run in development mode (HMR)

```powershell
npm run dev
```

Vite will show the local URL (typically http://localhost:5173). Open it in your browser.

## Build for production

```powershell
npm run build:tsc   # runs TypeScript build (tsc -b) then vite build (if this script exists)
npm run build       # vite build only
```

## Notes and troubleshooting
- If Vite fails to start because it loads a Vite config from another folder, use the included helper config `vite.design.config.ts` which points the root to the design folder while using the `frontend` node_modules.
- A few UI files were stubbed/removed during a cleanup pass. If you see import errors from `src/components/ui`, ensure the files `button`, `card`, `input`, `select`, `badge`, `table`, `textarea`, `progress`, `utils.ts` and `use-mobile.ts` are present. Those are required by the app.
- If TypeScript shows errors introduced earlier (temporary `any` uses), they are safe for local dev but should be fixed before pushing.

Want me to add a single PowerShell script to start both backend and frontend concurrently? I can add a `dev.ps1` that runs both servers in parallel.
