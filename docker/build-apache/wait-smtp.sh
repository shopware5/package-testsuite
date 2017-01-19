#!/usr/bin/env bash

echo "Stalling for smtp"
while ! nc -z smtp 1025; do sleep 1; done