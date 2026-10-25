#!/usr/bin/env bash
# Makes public/ the web root on Hostinger, once.
#
# Today the whole project sits in public_html (the folder the domain serves) and the root .htaccess
# rewrites every request into public/. Only that rule stands between visitors and .env, vendor/,
# storage/ and the backups. After the switch:
#
#   ~/domains/supplement-station.com/app          the project (moved, not copied)
#   ~/domains/supplement-station.com/public_html  -> app/public   (a symlink)
#
# so nothing outside public/ can be reached from the web at all.
#
#   bash scripts/switch-web-root.sh           show what would happen
#   bash scripts/switch-web-root.sh --go      switch (checks the site, switches back by itself on failure)
#   bash scripts/switch-web-root.sh --revert  put the project back in public_html
#
# Run it from the project folder, on a quiet moment: the site is in maintenance mode for a few seconds.
set -euo pipefail

PHP=${PHP:-php}
MODE=${1:-}
# Run from the project folder; the very first deploy runs a copy of this file fetched from GitHub
[ -f artisan ] || cd "$(dirname "$0")/.."
HERE=$(pwd -P)
DOMAIN_DIR=$(dirname "$HERE")
WEB="$DOMAIN_DIR/public_html"
APP="$DOMAIN_DIR/app"

env_value() { grep -E "^$1=" "$2/.env" | tail -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }

# public/storage must point at storage/app/public by a relative link so it survives the move
relink_storage() {
    local project=$1
    if [ -L "$project/public/storage" ]; then
        rm "$project/public/storage"
    fi
    if [ ! -e "$project/public/storage" ]; then
        ln -s ../storage/app/public "$project/public/storage"
    fi
    # a real folder at public/storage is left as it is
}

rebuild_caches() {
    # cached config holds absolute paths, so it must be rebuilt in the new place
    (cd "$1" && rm -f bootstrap/cache/config.php bootstrap/cache/routes-v7.php bootstrap/cache/events.php \
        && "$PHP" artisan optimize >/dev/null)
}

check_site() {
    local url bad=0 image
    url=$(env_value APP_URL "$1")
    image=$(cd "$1" && "$PHP" artisan tinker --execute='echo App\Models\ProductImage::value("url");' 2>/dev/null | tail -1)
    for path in / /shop /sitemap.xml ${image:+/$image}; do
        code=$(curl -s -o /dev/null -w '%{http_code}' -m 30 "$url$path") || code=000
        echo "  $code $path"
        [ "$code" = "200" ] || bad=1
    done
    # things that must never be served
    for path in /.env /composer.json /artisan /storage/logs/laravel.log /vendor/autoload.php /backups/; do
        code=$(curl -s -o /dev/null -w '%{http_code}' -m 30 "$url$path") || code=000
        echo "  $code $path (must not be 200)"
        [ "$code" != "200" ] || bad=1
    done
    return $bad
}

switch_to_public() {
    (cd "$WEB" && "$PHP" artisan down --retry=15 >/dev/null)
    mv "$WEB" "$APP"
    ln -s app/public "$WEB"
    relink_storage "$APP"
    rebuild_caches "$APP"
    (cd "$APP" && "$PHP" artisan up >/dev/null)
}

switch_back() {
    (cd "$APP" && "$PHP" artisan down --retry=15 >/dev/null) || true
    [ -L "$WEB" ] && rm "$WEB"
    mv "$APP" "$WEB"
    relink_storage "$WEB"
    rebuild_caches "$WEB"
    (cd "$WEB" && "$PHP" artisan up >/dev/null)
}

if [ "$MODE" = "--revert" ]; then
    [ -L "$WEB" ] && [ -d "$APP" ] || { echo "Nothing to revert: public_html is not a link to app/public."; exit 1; }
    switch_back
    echo "The project is back in public_html. Checking:"
    check_site "$WEB" || echo "A check failed; look at the lines above."
    exit 0
fi

[ "$HERE" = "$(cd "$WEB" 2>/dev/null && pwd -P)" ] || { echo "Already switched, or not run from public_html (here: $HERE)."; exit 1; }
[ ! -e "$APP" ] || { echo "$APP already exists; move it away first."; exit 1; }
[ -f "$HERE/public/index.php" ] || { echo "public/index.php not found."; exit 1; }
grep -q 'www\\.supplement-station' "$HERE/public/.htaccess" || { echo "public/.htaccess has no www redirect yet; deploy the latest code first."; exit 1; }

echo "Project now:  $WEB"
echo "Will become:  $APP, and public_html -> app/public"
if [ "$MODE" != "--go" ]; then
    echo "Nothing was changed. Run with --go to switch (it switches back by itself if the site fails)."
    exit 0
fi

switch_to_public
echo "Switched. Checking the site:"
if check_site "$APP"; then
    echo
    echo "Done. From now on run the scripts from $APP:"
    echo "  cd $APP && bash scripts/deploy.sh"
    echo "To undo: cd $APP && bash scripts/switch-web-root.sh --revert"
else
    echo
    echo "A check failed; switching back."
    switch_back
    check_site "$WEB" || true
    echo "Back as before. Nothing else was changed."
    exit 1
fi
