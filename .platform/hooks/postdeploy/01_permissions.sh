#!/bin/bash
set -e

APP_DIR="/var/app/current"

# Ensure uploads exists and is accessible
mkdir -p "$APP_DIR/uploads"
chown -R webapp:webapp "$APP_DIR/uploads"
find "$APP_DIR/uploads" -type d -exec chmod 775 {} \;
find "$APP_DIR/uploads" -type f -exec chmod 664 {} \;

echo "[postdeploy] uploads permissions ensured."


# Ensure data directory exists and is writable (for properties.json, team.json)
mkdir -p "$APP_DIR/data"
chown -R webapp:webapp "$APP_DIR/data"
find "$APP_DIR/data" -type d -exec chmod 775 {} \;
find "$APP_DIR/data" -type f -exec chmod 664 {} \;

echo "[postdeploy] data directory permissions ensured."
