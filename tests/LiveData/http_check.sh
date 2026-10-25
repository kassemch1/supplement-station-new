#!/bin/bash
# Usage: http_check.sh BASE_URL ADMIN_PW_FILE  -> checks public pages, cart + checkout, admin pages
B=$1; PW=$(cat "$2"); J=$(mktemp); A=$(mktemp); fail=0
code() { curl -s -o /dev/null -w "%{http_code}" -m 30 -b "$1" -c "$1" "${@:2}"; }
ok() { if [ "$2" = "$3" ]; then echo "OK   $2 $1"; else echo "FAIL $2 (want $3) $1"; fail=$((fail+1)); fi; }
for p in / /Shop "/Shop?category-sidebar=1&orderby=low-to-high" "/Shop?shop-search=whey" /products/2 /viewCart /Checkout /api/cart /api/recent-purchases /login /up; do ok "$p" "$(code $J -L "$B$p")" 200; done  # -L: old addresses redirect since Phase 4
# cart + checkout as a customer
html=$(curl -s -L -m 30 -b $J -c $J "$B/products/439"); tok=$(echo "$html" | grep -o 'name="csrf-token" content="[^"]*"' | head -1 | sed 's/.*content="//; s/"//')
ok "cart/add" "$(code $J -X POST -H "X-CSRF-TOKEN: $tok" -H 'Accept: application/json' --data 'product_id=439&quantity=2&selected_options={}' "$B/cart/add")" 200
items=$(curl -s -m 30 -b $J -c $J "$B/api/cart" | grep -o '"quantity":[0-9]*' | head -1); echo "     cart: $items"
chk=$(curl -s -L -m 30 -b $J -c $J "$B/Checkout"); ctok=$(echo "$chk" | grep -o 'name="checkout_token" value="[^"]*"' | sed 's/.*value="//; s/"//')
echo "     checkout_token on page: ${ctok:-<none>}"
order() { curl -s -m 30 -b $J -c $J -X POST -H "X-CSRF-TOKEN: $tok" -H 'Accept: application/json' --data "billing_first_name=SimDeploy+Test&billing_phone=03123456&billing_address_1=St&billing_city=Beirut&billing_email=sim@example.test&checkout_token=$ctok" "$B/place-order"; }
r1=$(order); r2=$(order); echo "     order #1: $r1"; echo "     order #2: $r2"
echo "$r1" | grep -q '"success":true' && ok "place-order" 200 200 || ok "place-order" fail 200
# admin
lhtml=$(curl -s -m 30 -b $A -c $A "$B/login"); ltok=$(echo "$lhtml" | grep -o 'name="_token" value="[^"]*"' | head -1 | sed 's/.*value="//; s/"//')
ok "admin login" "$(code $A -X POST --data-urlencode "_token=$ltok" --data-urlencode "email=sim-admin@example.test" --data-urlencode "password=$PW" "$B/auth")" 302
for p in /admin /admin/orders/pending /admin/orders/delivered /admin/orders/12 /admin/orders/12/details /admin/orders/13 /manageProducts /manageProducts/create /manageCategories /manageOptions /manageProductsOptions /admin/coupons /admin/special-offers /admin/subscribers /manageBanner /manageFaq /managePlan; do ok "$p" "$(code $A "$B$p")" 200; done
# Phase 1 info (expected to differ between old and new code)
echo "INFO sold-out page /products/213: $(code $J "$B/products/213")"
echo "INFO add sold-out product 213: $(curl -s -m 30 -b $J -c $J -X POST -H "X-CSRF-TOKEN: $tok" -H "Accept: application/json" --data "product_id=213&quantity=1&selected_options={}" "$B/cart/add" | head -c 120)"
echo "INFO admin cancelled list: $(code $A "$B/admin/orders/cancelled")  archived filter: $(code $A "$B/manageProducts?stock=archived")"
echo "== failures: $fail"; rm -f $J $A
