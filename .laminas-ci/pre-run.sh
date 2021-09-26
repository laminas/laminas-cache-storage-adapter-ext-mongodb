#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

if ! [[ "${PHP_VERSION}" =~ 8\.1 ]]; then
    exit 0;
fi

composer require mongodb/mongodb:dev-master --ignore-platform-req=php
