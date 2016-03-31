#!/bin/bash
# Setup Directories and Versions
if [ -n "${TRAVIS_BUILD_DIR}" ]; then
    # We are using travis and must set the directories
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export BUILD_DIR=${TRAVIS_BUILD_DIR}
fi
export CI_DIR = ${SPIDER_DIR}/CI
source ${CI_DIR}/versions.sh

#export NEO4J_VERSION="2.2.4"
#
## Add environment java vars
#export JAVA_HOME=/usr/lib/jvm/java-8-oracle
#export JRE_HOME=/usr/lib/jvm/java-8-oracle

# install Neo4j locally:
wget -O $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $HOME/


#$HOME/neo4j-community-$NEO4J_VERSION/bin/neo4j start
#sleep 10
#
## changing password:
#curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"
