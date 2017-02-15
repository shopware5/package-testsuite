#!/usr/bin/env bash

retries=30
while ! nc -z mysql 3306
do
    ((c++)) && ((c==retries)) && break
    echo -n .
    sleep 1;
done