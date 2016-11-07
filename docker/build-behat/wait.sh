#!/usr/bin/env bash

echo "Stalling for apache"
while ! nc -z shopware-test.localhost 3306; do sleep 1; done