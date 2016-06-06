#!/usr/bin/env bash

bin/spider-dev up
export WAIT_FOR=true
php vendor/bin/phpunit -c phpunit.xml.dist