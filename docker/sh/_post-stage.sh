#!/usr/bin/env sh
set -eu

echo "Cleanup"
[ -f "${ENV_TESTS:-}" ] && rm -f "$ENV_TESTS"
[ -f "${BEHAT:-}" ] && rm -f "$BEHAT"

rm -f ../tests/clean_db.sql

compose exec apache chown -R "$(id -u):$(id -g)" /tests

compose down -v --remove-orphans
compose rm -v --force

unalias compose
