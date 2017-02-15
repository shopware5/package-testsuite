#!/usr/bin/env bash

retries=30
while ! nc -z smtp 1025
do
    ((c++)) && ((c==retries)) && break
    echo -n .
    sleep 1;
done