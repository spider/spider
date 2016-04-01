#!/bin/bash

# Setup Directories and Versions
if [ -n "${TRAVIS_BUILD_DIR}" ]; then
    # We are using travis and must set the directories
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export BUILD_DIR=${TRAVIS_BUILD_DIR}
fi
export CI_DIR = ${SPIDER_DIR}/CI
source ${CI_DIR}/versions.sh

#export GREMLINSERVER_VERSION="3.0.2"
#
## Load the Vagrant context if there is none present
#if [ -z ${TRAVIS_BUILD_DIR+x} ]
#    then
#    # Using vagrant
#    export BUILD_DIR="/home/vagrant"
#    export SPIDER_DIR="/vagrant"
#
#else
#    # Using Travis UPDATE
#    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
#    export BUILD_DIR=${TRAVIS_BUILD_DIR}
#fi


# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Install gremlin-server
wget --no-check-certificate -O $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/$GREMLINSERVER_VERSION-incubating/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip
unzip $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip -d $HOME/

# get gremlin-server configuration files
cp $SPIDER_DIR/CI/gremlin-server/gremlin-spider-script.groovy $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/scripts/
cp $SPIDER_DIR/CI/gremlin-server/gremlin-server-spider.yaml $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/conf/

# get neo4j dependencies
cd $HOME/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating
cd $BUILD_DIR
