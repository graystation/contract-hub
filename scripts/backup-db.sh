#!/bin/bash
# Database backup script for Contract Hub
# Usage: bash scripts/backup-db.sh

BACKUP_DIR="storage/backups/db"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILE="${BACKUP_DIR}/contract_hub_${TIMESTAMP}.sql"

mkdir -p "$BACKUP_DIR"

/Applications/XAMPP/xamppfiles/bin/mysqldump \
  --socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock \
  -u root \
  contract_hub > "$FILE"

echo "✓ Backup saved: $FILE"
echo "  Size: $(du -sh $FILE | cut -f1)"
