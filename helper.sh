#!/bin/bash
export COMPOSE_PROJECT_NAME="testing"

function runmink() {
  path="$1"
  prefix="tests"
  sanitized_dir=${path#"$prefix"}
  docker compose  -f docker/docker-compose.yml -f docker/docker-compose.local.yml run --rm behat --format=pretty --out=std --format=junit --out=/logs/mink "$sanitized_dir"
}

function delmink() {
  (cd docker || exit; docker compose down -v)
}

function installmink() {
  (cd docker || exit; ./_local_manual_testing.sh $COMPOSE_PROJECT_NAME)
}

case "$1" in
  "runmink")
    runmink "$2"
  ;;
  "delmink")
    delmink
  ;;
  "installmink")
    installmink
  ;;
esac
