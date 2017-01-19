#!/usr/bin/env bash

echo "Stalling for mysql"
while ! nc -z mysql 3306; do sleep 1; done