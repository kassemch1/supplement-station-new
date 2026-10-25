Checks run against the local database loaded with a dump of the live one, before each release. They
use the real data (DatabaseTransactions roll every change back), so they catch what the permanent
suite in tests/Feature cannot: the orders recovered in Phase 0, slugs of every real product, and so on.

Run them (PHP 8.3, Xdebug off), with the database from .env:

    XDEBUG_MODE=off php vendor/bin/phpunit -c phpunit.live.xml
    XDEBUG_MODE=off php vendor/bin/phpunit -c phpunit.live.xml tests/LiveData/Phase1Test.php

The normal `phpunit` run never includes them (phpunit.xml points at SQLite :memory:).

http_check.sh BASE_URL ADMIN_PW_FILE: HTTP checks used for deploy simulations (needs an admin user
sim-admin@example.test with the password in ADMIN_PW_FILE, created in the simulation database only).
