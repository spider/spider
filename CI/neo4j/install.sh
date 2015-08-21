#!/bin/bash

# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# install Neo4j locally:
wget -O $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $HOME/
$HOME/neo4j-community-$NEO4J_VERSION/bin/neo4j start
sleep 10

# changing password:
curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"
