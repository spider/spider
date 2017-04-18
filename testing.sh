#!/usr/bin/env bash

echo "in testing...."

#export TRAVIS_BUILD_DIR="/home/travis"

source ./myci/travis.sh
source ./myci/provision.sh # shared
source ./myci/startup.sh # shared

echo "...done with testing"