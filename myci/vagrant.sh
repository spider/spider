#!/usr/bin/env bash
#
## Setup the directories, use these from now on
#export SPIDER_DIR="/vagrant" # Where does the vagrant library live?
#export INSTALL_DIR="${SPIDER_DIR}/installed" # Where do the executables live?
#export CI_DIR="${SPIDER_DIR}/myci" # Where do the provisioning files live?
#export BUILD_DIR=$(pwd)  # Where does the build start from?

# Import the Versions
source ${CI_DIR}/versions.sh # Relative to .travis.yaml and Vagrantfile

export GREMLIN_DIR="${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating"
export NEO4J_DIR=$INSTALL_DIR/neo4j-community-$NEO4J_VERSION
export ORIENT_DIR="${INSTALL_DIR}/orientdb-community-$ORIENT_VERSION"
