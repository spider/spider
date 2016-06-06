#!/usr/bin/env bash

bin/spider-dev up
sleep 60
php vendor/bin/phpunit -c phpunit.xml.dist