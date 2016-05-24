#!/usr/bin/env bash

bin/spider-dev up
sleep 120
php vendor/bin/phpunit -c phpunit.xml.dist