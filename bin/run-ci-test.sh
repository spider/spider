#!/usr/bin/env bash

bin/spider-dev up
sleep 40
php vendor/bin/phpunit -c phpunit.xml.dist