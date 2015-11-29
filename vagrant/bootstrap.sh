#!/bin/bash
echo "------------ PROVISIONING SYSTEM FROM BOOTSTRAP ------------"

# Set variables
export NEO4J_VERSION="2.2.4"
export GREMLINSERVER_VERSION="3.0.2"
export ORIENT_VERSION="2.1.6"

export INSTALL_DIR="/home/vagrant"
export VAGRANT_DIR="/vagrant"
export BOOTSTRAP_DIR="$VAGRANT_DIR/vagrant"


### INSTALL jdk8
## Get dependencies (for adding repos)
sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:webupd8team/java
sudo apt-get update

## install oracle jdk 8
# no interaction
echo debconf shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | /usr/bin/debconf-set-selections

# run installer
sudo apt-get install -y oracle-java8-installer
sudo update-alternatives --auto java
sudo update-alternatives --auto javac

## add to environment
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle


### install gremlin-server
export GREMLIN_DIR="apache-gremlin-server-$GREMLINSERVER_VERSION-incubating"

# download and unzip
wget --no-check-certificate -O $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/$GREMLINSERVER_VERSION-incubating/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip
unzip $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip -d $INSTALL_DIR/

# get gremlin-server configuration files
cp $BOOTSTRAP_DIR/gremlin-spider-script.groovy $INSTALL_DIR/$GREMLIN_DIR/scripts/
cp $BOOTSTRAP_DIR/gremlin-server-spider.yaml $INSTALL_DIR/$GREMLIN_DIR/conf/

# get neo4j dependencies
#$INSTALL_DIR/$GREMLIN_DIR/bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating

# get neo4j dependencies
cd $INSTALL_DIR/$GREMLIN_DIR
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating
sleep 30
cd $VAGRANT_DIR

### install neo4j
# install Neo4j locally:
wget -O $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $INSTALL_DIR/

### install orient
# Download orient
wget -O $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz wget http://www.orientechnologies.com/download.php?file=orientdb-community-$ORIENT_VERSION.tar.gz
tar -xzf $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz -C $INSTALL_DIR/

# update config with correct user/password
#sed -i '(password=".*?") c\password="root"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml
# sed -i '/<users>/a <user name="root" password="root" resources="*"><\/user>' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml
sed -i '/ORIENTDB_DIR="YOUR_ORIENTDB_INSTALLATION_PATH"/ c\ORIENTDB_DIR="'$INSTALL_DIR'/orientdb-community-'$ORIENT_VERSION'"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/orientdb.sh
sed -i '/ORIENTDB_USER="USER_YOU_WANT_ORIENTDB_RUN_WITH"/ c\ORIENTDB_USER="root"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/orientdb.sh

### install X-debug for code coverage
sudo apt-get install php5-xdebug

echo "------------ END: PROVISIONING SYSTEM FROM BOOTSTRAP ------------"
