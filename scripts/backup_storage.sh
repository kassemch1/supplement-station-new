#!/bin/bash

# Define variables
BACKUP_DIR="/home/tawwabaw/supplement-station.com/public/storage"
BACKUP_NAME="storage_backup_$(date +'%Y%m%d_%H%M%S').zip"
BACKUP_PATH="/home/tawwabaw/supplement-station.com/backups/$BACKUP_NAME"

# Create a zip backup of the storage directory
/usr/bin/zip -r "$BACKUP_PATH" "$BACKUP_DIR"

# Optionally, remove backups older than 30 days
find /home/tawwabaw/supplement-station.com/backups -name 'storage_backup_*.zip' -mtime +30 -exec rm {} \;

echo "Backup created successfully: $BACKUP_NAME"
