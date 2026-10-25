#!/usr/bin/env bash
# Deploys origin/main to the live site, with a way back.
#
#   bash scripts/deploy.sh            show what would be deployed, change nothing
#   bash scripts/deploy.sh --go       deploy
#
# Run it on the server from the project folder. The first time (the server has no scripts/ yet):
#   git fetch origin main && git show origin/main:scripts/deploy.sh > ~/deploy.sh && bash ~/deploy.sh
#
# Every deploy:
#   1. refuses to run if files on the server were edited by hand (git status not clean)
#   2. tags the running commit (live-YYYYmmdd-HHMMSS) and dumps the database to ~/backups
#   3. pulls, installs packages, migrates, rebuilds the caches
#   4. checks the home and shop pages answer 200
#   5. writes the tag, commits, migration batch and backup to storage/logs/deploys.log (before changing anything)
# To go back: bash scripts/rollback.sh (see that file).
set -euo pipefail

# Run from the project folder; the very first deploy runs a copy of this file fetched from GitHub
[ -f artisan ] || cd "$(dirname "$0")/.."
ROOT=$(pwd)
PHP=${PHP:-php}
COMPOSER_BIN=${COMPOSER_BIN:-composer}
BRANCH=main
STAMP=$(date +%Y%m%d-%H%M%S)
TAG="live-$STAMP"
BACKUPS="$HOME/backups"
LOG="$ROOT/storage/logs/deploys.log"
GO=0
[ "${1:-}" = "--go" ] && GO=1

say() { printf '\n== %s\n' "$*"; }
fail() { printf '\nSTOPPED: %s\n' "$*" >&2; exit 1; }
env_value() { grep -E "^$1=" .env | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"; }

# ---------- checks (nothing is changed) ----------

[ -f artisan ] && [ -f .env ] || fail "run this from the project folder (artisan and .env not found)"
[ "$(git rev-parse --abbrev-ref HEAD)" = "$BRANCH" ] || fail "the server is on branch $(git rev-parse --abbrev-ref HEAD), expected $BRANCH"
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    git status --short --untracked-files=no
    fail "files tracked by git were changed on the server; save or undo those changes first"
fi
[ "$(env_value APP_DEBUG)" = "false" ] || echo "WARNING: APP_DEBUG is not false in .env (visitors would see error details)"

say "Fetching origin/$BRANCH"
git fetch --quiet origin "$BRANCH"
OLD=$(git rev-parse HEAD)
NEW=$(git rev-parse "origin/$BRANCH")
if [ "$OLD" = "$NEW" ]; then
    echo "Already up to date ($(git log -1 --format='%h %s'))."
    exit 0
fi
git merge-base --is-ancestor "$OLD" "$NEW" || fail "origin/$BRANCH is not ahead of the server's commit (history was rewritten?); deploy by hand"

echo "Running now: $(git log -1 --format='%h %s' "$OLD")"
echo "Will deploy:"
git log --format='  %h %s' "$OLD..$NEW"
NEW_MIGRATIONS=$(git diff --name-only --diff-filter=A "$OLD" "$NEW" -- database/migrations | wc -l | tr -d ' ')
echo "New migrations: $NEW_MIGRATIONS"
git diff --quiet "$OLD" "$NEW" -- composer.lock && echo "Packages: unchanged" || echo "Packages: composer.lock changed"

if [ $GO -eq 0 ]; then
    echo
    echo "Nothing was changed. Run again with --go to deploy."
    exit 0
fi

# ---------- restore point ----------

say "Restore point: tag $TAG at $(git rev-parse --short "$OLD")"
git tag "$TAG" "$OLD"

say "Database backup"
mkdir -p "$BACKUPS"
chmod 700 "$BACKUPS"
DUMP="$BACKUPS/db-$STAMP.sql.gz"
MYSQL_PWD=$(env_value DB_PASSWORD) mysqldump --single-transaction --quick --routines --no-tablespaces \
    -h "$(env_value DB_HOST)" -P "$(env_value DB_PORT)" -u "$(env_value DB_USERNAME)" "$(env_value DB_DATABASE)" | gzip > "$DUMP"
[ -s "$DUMP" ] || fail "the database backup is empty; nothing was deployed"
chmod 600 "$DUMP"
echo "Saved $DUMP ($(du -h "$DUMP" | cut -f1))"

# Recorded before anything changes, so rollback.sh finds this deploy even if it fails half way.
# batch = the last migration batch before this deploy: rollback.sh undoes every batch after it.
BATCH=$("$PHP" artisan tinker --execute='echo (int) DB::table("migrations")->max("batch");' 2>/dev/null | tail -1)
[[ "$BATCH" =~ ^[0-9]+$ ]] || fail "could not read the migration batch; nothing was deployed"
mkdir -p "$(dirname "$LOG")"
echo "$STAMP tag=$TAG from=$(git rev-parse --short "$OLD") to=$(git rev-parse --short "$NEW") batch=$BATCH backup=$DUMP" >> "$LOG"

# From here on, a failure takes the site out of maintenance mode and says how to go back
on_error() {
    "$PHP" artisan up >/dev/null 2>&1 || true
    echo
    echo "DEPLOY FAILED at the step above. The site is back up, possibly half updated."
    echo "To return to the version that was running:  bash scripts/rollback.sh $TAG"
    echo "Database backup: $DUMP"
}
trap on_error ERR

# ---------- deploy ----------

say "Maintenance mode"
"$PHP" artisan down --retry=15

say "Pulling $(git rev-parse --short "$NEW")"
git merge --ff-only --quiet "$NEW"

say "Packages"
"$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction --no-progress

say "Migrations"
"$PHP" artisan migrate --force

say "Caches"
"$PHP" artisan optimize:clear >/dev/null
"$PHP" artisan optimize
# uploaded pictures: public/storage -> storage/app/public (relative, so the project can move)
[ -e public/storage ] || ln -s ../storage/app/public public/storage

"$PHP" artisan up
trap - ERR


# ---------- check ----------

say "Checking the site"
URL=$(env_value APP_URL)
bad=0
for path in / /shop /cart /sitemap.xml; do
    code=$(curl -s -o /dev/null -w '%{http_code}' -m 30 "$URL$path") || code=000
    echo "  $code $path"
    [ "$code" = "200" ] || bad=1
done

echo
if [ $bad -eq 0 ]; then
    echo "Deployed $(git log -1 --format='%h %s'). Purge the Hostinger CDN cache if pages look old."
else
    echo "A page did not answer 200. Check it now; to go back: bash scripts/rollback.sh $TAG"
fi
echo "Rollback, if ever needed: bash scripts/rollback.sh $TAG"
