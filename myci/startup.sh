#!/bin/bash
#echo "------------ STARTING UP SYSTEM ------------"

#echo "Neo: ${NEO4J_VERSION}"
#echo "Gremlin: ${GREMLINSERVER_VERSION}"
#echo "Orient: ${ORIENT_VERSION}"
#
#echo "------------ DONE STARTING UP SYSTEM ------------"

# Load the Vagrant context if there is none present
if [ -z ${TRAVIS_BUILD_DIR+x} ]
    then
    # Using vagrant
    export SPIDER_DIR="/vagrant"
    export INSTALL_DIR="/home/vagrant"
else
    # Using Travis UPDATE
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export INSTALL_DIR=${HOME}
fi

# Load the versions and directories
export CI_DIR=${SPIDER_DIR}/myci
export BUILD_DIR=$(pwd)
source $CI_DIR/versions.sh

## start gremlin-server
cd $GREMLIN_DIR
sudo bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd $BUILD_DIR
sleep 30

## start neo4j
sudo $NEO4J_DIR/bin/neo4j start
sleep 15

# changing password:
sudo curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"

## start orient to initially and properly set up the orientdb-server-config.xml file
sudo nohup $ORIENT_DIR/bin/orientdb.sh start

sleep 15

## stop orient
sudo nohup $ORIENT_DIR/bin/orientdb.sh stop

sleep 15

## set up the password for root
sed -i 's/password=".*" name="root"/password="root"  name="root"/' $ORIENT_DIR/config/orientdb-server-config.xml

## restart the orient server
sudo nohup $ORIENT_DIR/bin/orientdb.sh start

sleep 15