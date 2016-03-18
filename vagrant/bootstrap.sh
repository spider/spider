#!/bin/bash
echo "------------ PROVISIONING SYSTEM FROM BOOTSTRAP ------------"

# Set variables
export NEO4J_VERSION="2.2.4"
export GREMLINSERVER_VERSION="3.0.2"
export ORIENT_VERSION="2.1.6"

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"
export BOOTSTRAP_DIR="$VAGRANT_DIR/vagrant"
export INITIAL_BUILD_DIR=$(pwd)



echo "------------ END: PROVISIONING SYSTEM FROM BOOTSTRAP ------------"
