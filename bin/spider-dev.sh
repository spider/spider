#!/usr/bin/env bash

# Uses flags or defaults to set environment variables for versions
# before executing `docker-compose up -d`

# looks like
# `ORIENT_VERSION=2.1.6; NEO4J_VERSION=1.7.3; PHP_VERSION=5.6; docker-compose up -d` to start services
# `docker-compose run php "cd /spider; vendor/bin/phpunit"` to run all tests
