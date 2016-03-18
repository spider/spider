#!/bin/bash
#echo "------------ PROVISIONING SYSTEM FROM BOOTSTRAP ------------"
#echo "Spider Dir: ${SPIDER_DIR}"
#echo "Install Dir: ${INSTALL_DIR}"
#echo "CI Dir: ${CI_DIR}"
#echo "BUILD Dir: ${BUILD_DIR}"
#echo "------------ END: PROVISIONING SYSTEM FROM BOOTSTRAP ------------"

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

### INSTALL PHP AND TOOLS ###
sudo apt-get update #> /dev/null
apt-get install software-properties-common python-software-properties -y #> /dev/null
add-apt-repository ppa:ondrej/php5-5.6 -y #> /dev/null
apt-get update #> /dev/null

apt-get install php5 -y #> /dev/null

sudo apt-get install curl php5-curl php5-gd php5-mcrypt -y #> /dev/null
sudo apt-get install php5-xdebug #> /dev/null

curl --silent https://getcomposer.org/installer | php #> /dev/null 2>&1
mv composer.phar /usr/local/bin/composer

alias phpunit=/vagrant/vendor/bin/phpunit


#### INSTALL JDK8
### Add Repository
sudo add-apt-repository -y ppa:webupd8team/java
sudo apt-get update

### Install oracle jdk 8
# no interaction
echo debconf shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections
echo debconf shared/accepted-oracle-license-v1-1 seen true | /usr/bin/debconf-set-selections

## Run installer
sudo apt-get install -y oracle-java8-installer
sudo update-alternatives --auto java
sudo update-alternatives --auto javac

### Add to environment
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle


### INSTALL GREMLIN SERVER
## download and unzip
wget --no-check-certificate -O $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/$GREMLINSERVER_VERSION-incubating/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip
unzip $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip -d $INSTALL_DIR/

## get gremlin-server configuration files
cp $CI_DIR/gremlin-spider-script.groovy $GREMLIN_DIR/scripts/
cp $CI_DIR/gremlin-server-spider.yaml $GREMLIN_DIR/conf/

# get neo4j dependencies
cd $GREMLIN_DIR
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating
sleep 30
cd $BUILD_DIR


#### INSTALL NEO4J
## Download
#wget -O $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
#tar -xzf $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $INSTALL_DIR/
#
#sed -i 's/#org.neo4j.server.webserver.address=0.0.0.0/org.neo4j.server.webserver.address=0.0.0.0/' $NEO4J_DIR/conf/neo4j-server.properties


### install neo4j
# install Neo4j locally:
wget -O $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $INSTALL_DIR/

sed -i 's/#org.neo4j.server.webserver.address=0.0.0.0/org.neo4j.server.webserver.address=0.0.0.0/' $INSTALL_DIR/neo4j-community-$NEO4J_VERSION/conf/neo4j-server.properties


### INSTALL ORIENT DB
# Download orient
wget -O $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz wget http://www.orientechnologies.com/download.php?file=orientdb-community-$ORIENT_VERSION.tar.gz
tar -xzf $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz -C $INSTALL_DIR/

### fix to make sure the orient install is also owned by root
chown -R root:root $ORIENT_DIR

### update server.sh with correct user and path
#sed -i '(password=".*?") c\password="root"' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml
# sed -i '/<users>/a <user name="root" password="root" resources="*"><\/user>' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml
sed -i '/ORIENTDB_DIR="YOUR_ORIENTDB_INSTALLATION_PATH"/ c\ORIENTDB_DIR="'$ORIENT_DIR'"' $ORIENT_DIR/bin/orientdb.sh
sed -i '/ORIENTDB_USER="USER_YOU_WANT_ORIENTDB_RUN_WITH"/ c\ORIENTDB_USER="root"' $ORIENT_DIR/bin/orientdb.sh
