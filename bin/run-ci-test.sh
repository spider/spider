#!/usr/bin/env bash

bin/spider-dev up
sleep 60
php WAIT_FOR=true vendor/bin/phpunit -c phpunit.xml.dist