#!/usr/bin/env bash
set -euo pipefail

DEST=${1:-../vessel-distribution}
# Paths to export; adjust as needed
PATHS=(modules/catalog shared apps/vessel-web apps/vessel-docs resources config bootstrap)

mkdir -p "$DEST"

echo "Exporting paths: ${PATHS[*]} -> $DEST"
git archive --format=tar HEAD "${PATHS[@]}" | tar -xf - -C "$DEST"

(
  cd "$DEST"
  echo "Exported. Git status:"
  git status --short || true
)
