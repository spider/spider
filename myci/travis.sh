#!/usr/bin/env bash

# Set the Build Context
export CONTEXT="travis"

# Set the directories
export SPIDER_DIR=${TRAVIS_BUILD_DIR} # Where does the vagrant library live?
export INSTALL_DIR="${TRAVIS_BUILD_DIR}/installed" # Where do the executables live?
export CI_DIR="${SPIDER_DIR}/myci" # Where do the provisioning files live?
export BUILD_DIR=${TRAVIS_BUILD_DIR}  # Where does the build start from?

# Import the Versions
source ${CI_DIR}/versions.sh

# For Travis, we want to provision AND startup
source ${CI_DIR}/provision.sh
source ${CI_DIR}/startup.sh