#!/usr/bin/env bash
export NEO4J_VERSION="2.2.4"
export GREMLINSERVER_VERSION="3.0.2"
export ORIENT_VERSION="2.1.6"

export GREMLIN_DIR=${INSTALL_DIR}/apache-gremlin-server-${GREMLINSERVER_VERSION}-incubating
export NEO4J_DIR=$INSTALL_DIR/neo4j-community-$NEO4J_VERSION
export ORIENT_DIR=${INSTALL_DIR}/orientdb-community-$ORIENT_VERSION

## CREATE THE INSTALL DIRECTORY
mkdir $INSTALL_DIR

echo $INSTALL_DIR