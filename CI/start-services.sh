#!/bin/bash

# Setup Directories and Versions
if [ -n "${TRAVIS_BUILD_DIR}" ]; then
    # We are using travis and must set the directories
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export BUILD_DIR=${TRAVIS_BUILD_DIR}
fi
export CI_DIR = ${SPIDER_DIR}/CI
source ${CI_DIR}/versions.sh


## Start OrientDB
$HOME/orientdb-community-$ORIENT_VERSION/bin/server.sh > /dev/null 2>&1 &
sleep 15


## Start Gremlin
cd $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating
bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd $BUILD_DIR


## Start Neo
$HOME/neo4j-community-$NEO4J_VERSION/bin/neo4j start
sleep 10

# changing password:
curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"
