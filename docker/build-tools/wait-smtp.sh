#!/usr/bin/env bash

while ! nc -z smtp 1025; do sleep 1; done