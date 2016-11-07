#!/usr/bin/env bash

executable=$1; shift

/wait.sh

exec "$executable" ${1+"$@"};