#!/bin/bash

export GREMLINSERVER_VERSION="3.0.2"
export ORIENT_VERSION="2.1.6"
export NEO4J_VERSION="2.2.4"

# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Load the Vagrant context if there is none present
if [ -z ${TRAVIS_BUILD_DIR+x} ]
    then
    # Using vagrant
    export BUILD_DIR="/home/vagrant"
    export SPIDER_DIR="/vagrant"

else
    # Using Travis UPDATE
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export BUILD_DIR=${TRAVIS_BUILD_DIR}
fi


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
