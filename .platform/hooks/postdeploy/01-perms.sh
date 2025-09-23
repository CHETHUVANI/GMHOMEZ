#!/bin/bash
set -euo pipefail
APP_ROOT="/var/app/current"
mkdir -p "$APP_ROOT/data/builders" "$APP_ROOT/uploads/builders"
chown -R webapp:webapp "$APP_ROOT/data" "$APP_ROOT/uploads" || true
chmod -R 775 "$APP_ROOT/data" "$APP_ROOT/uploads" || true
echo "[postdeploy] builder pack permissions set"
