#!/usr/bin/env bash
# Returns the live site to the version that ran before a deploy.
#
#   bash scripts/rollback.sh                           show the last deploy and what going back would do
#   bash scripts/rollback.sh live-20261025-120000 --go
#
# Order matters: the migrations of that deploy are rolled back with the NEW code (which has their
# down() methods), and only then is the old code checked out. Data the admin entered is kept; the
# database dump taken by that deploy (~/backups) is there if anything goes wrong.
set -euo pipefail

# Run from the project folder; a copy of this file fetched from GitHub works too
[ -f artisan ] || cd "$(dirname "$0")/.."
PHP=${PHP:-php}
COMPOSER_BIN=${COMPOSER_BIN:-composer}
LOG=storage/logs/deploys.log

[ -f "$LOG" ] || { echo "No deploys recorded in $LOG."; exit 1; }

TAG=""
GO=0
for arg in "$@"; do
    if [ "$arg" = "--go" ]; then GO=1; else TAG=$arg; fi
done
[ -n "$TAG" ] || TAG=$(grep ' tag=' "$LOG" | tail -1 | sed -n 's/.* tag=\([^ ]*\).*/\1/p')

LINE=$(grep " tag=$TAG " "$LOG" | tail -1) || { echo "No deploy with tag $TAG in $LOG"; exit 1; }
BATCH=$(echo "$LINE" | sed -n 's/.* batch=\([0-9]*\).*/\1/p')
BACKUP=$(echo "$LINE" | sed -n 's/.* backup=\([^ ]*\).*/\1/p')
git rev-parse -q --verify "refs/tags/$TAG" >/dev/null || { echo "Tag $TAG not found"; exit 1; }
[ -n "$BATCH" ] || { echo "The log line has no migration batch: $LINE"; exit 1; }

current_batch() {
    "$PHP" artisan tinker --execute='echo (int) DB::table("migrations")->max("batch");' 2>/dev/null | tail -1
}
NOW=$(current_batch)

echo "Deploy:        $LINE"
echo "Running now:   $(git log -1 --format='%h %s')"
echo "Going back to: $(git log -1 --format='%h %s' "$TAG")"
echo "Migration batches to roll back first: $((NOW - BATCH)) (now $NOW, before the deploy $BATCH)"
echo "Database dump from before that deploy: $BACKUP"
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    git status --short --untracked-files=no
    echo "STOPPED: files tracked by git were changed on the server; save or undo those changes first"
    exit 1
fi

if [ $GO -eq 0 ]; then
    echo
    echo "Nothing was changed. Run:  bash scripts/rollback.sh $TAG --go"
    exit 0
fi

"$PHP" artisan down --retry=15
trap '"$PHP" artisan up >/dev/null 2>&1 || true; echo "ROLLBACK FAILED at the step above; the site is up. Database dump: $BACKUP"' ERR

while [ "$(current_batch)" -gt "$BATCH" ]; do
    echo "== Rolling back migration batch $(current_batch)"
    "$PHP" artisan migrate:rollback --force
done

echo "== Checking out $TAG"
git reset --keep "$TAG"

echo "== Packages and caches"
"$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --no-progress
"$PHP" artisan optimize:clear >/dev/null
"$PHP" artisan optimize

"$PHP" artisan up
trap - ERR
echo "$(date +%Y%m%d-%H%M%S) rollback to=$TAG ($(git rev-parse --short HEAD))" >> "$LOG"
echo
echo "Back on $(git log -1 --format='%h %s'). Purge the Hostinger CDN cache."
echo "The next 'bash scripts/deploy.sh --go' deploys origin/main again."
