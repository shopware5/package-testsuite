#!/usr/bin/env bash

while ! nc -z mysql 3306; do sleep 1; done