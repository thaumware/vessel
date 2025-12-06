# Distribution Workflow (Internal -> Public)

This repository is the source-of-truth (internal dev). The public "distribution" repo should receive exported code (starter) with no cross-module references beyond `shared`.

## Quick Workflow

1) Ensure main is green and up to date.
2) Export the desired paths to the distribution repo:
   ```bash
   # from this repo root (git bash)
   DEST=../vessel-distribution   # clone of the public repo
   mkdir -p "$DEST"
   git archive --format=tar HEAD modules/catalog apps/vessel-web apps/vessel-docs shared resources config bootstrap | tar -xf - -C "$DEST"
   ```
   Adjust paths to include only what you want to ship (core + selected modules).
3) In the distribution repo:
   ```bash
   cd "$DEST"
   git status
   git add -A
   git commit -m "chore: sync from source"  # or amend existing sync commit
   git tag -a vX.Y.Z -m "Release vX.Y.Z"    # optional, for versioning
   git push origin main --tags
   ```
4) Users pull or `git clone` the distribution repo. The `app:update` command already handles pull+composer+migrate in dev/stage.

## Receiving Community PRs

- Accept PRs on the distribution repo (public). After merging there:
  ```bash
  git remote add public ../vessel-distribution   # once
  git fetch public
  git cherry-pick <merge-commit-sha>             # into this internal repo
  ```
- Resolve conflicts here, run tests, then the next export will include the fix.

## Branch & Tag Strategy

- Internal repo: normal feature branches → main.
- Distribution repo: keep main fast-forward or rebase onto exported state.
- Tag releases in distribution (vX.Y.Z). Use SemVer. Consumers can track tags or main.

## Contents to Ship (suggested)

- `shared/` (core utilities, middleware, contracts).
- Selected modules (e.g., stock, taxonomy, uom, locations) without cross-module references beyond shared.
- `apps/vessel-web` and docs if you want to provide UI/demo.
- Remove secrets, local .env, caches, and vendor.

## Safety Checklist Before Export

- `.env` and secrets removed.
- `storage/` cleaned (no logs/uploads).
- `vendor/` excluded (git archive does this by default).
- Tests passing (`php artisan test`).

## Optional Helpers

Add a helper script (example):
```bash
#!/usr/bin/env bash
set -euo pipefail
DEST=${1:-../vessel-distribution}
PATHS=(modules/catalog shared apps/vessel-web apps/vessel-docs resources config bootstrap)
mkdir -p "$DEST"
git archive --format=tar HEAD "${PATHS[@]}" | tar -xf - -C "$DEST"
(
  cd "$DEST"
  git add -A
  git status --short
)
```
Place it under `scripts/export_distribution.sh` if you want it committed.

## Collaboration Notes

- Keep dependencies between modules flowing through `shared` only.
- If a module needs a shared contract, move the contract to `shared` and let modules implement it independently.
- Prefer small, focused PRs on the distribution repo to ease cherry-picks back here.
