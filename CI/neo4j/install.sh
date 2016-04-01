#!/bin/bash
export NEO4J_VERSION="2.2.4"

# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# install Neo4j locally:
wget -O $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $HOME/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $HOME/