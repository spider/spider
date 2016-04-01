#!/bin/bash

export ORIENT_VERSION="2.1.6"

# Add environment java vars
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle

# Download orient
wget -O $HOME/orientdb-community-$ORIENT_VERSION.tar.gz wget http://www.orientechnologies.com/download.php?file=orientdb-community-$ORIENT_VERSION.tar.gz
tar -xzf $HOME/orientdb-community-$ORIENT_VERSION.tar.gz -C $HOME/

#update config with correct user/password
sed -i '/<users>/a <user name="root" password="root" resources="*"><\/user>' $HOME/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml
