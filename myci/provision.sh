#!/bin/bash
echo "------------ PROVISIONING SYSTEM FROM provision.sh ------------"

### SETUP
# Load the Vagrant context if there is none present
if [ -z ${TRAVIS_BUILD_DIR+x} ]
    then
    # Using vagrant
    export CONTEXT="VAGRANT"
    export SPIDER_DIR="/vagrant"
    export INSTALL_DIR="/home/vagrant"
else
    # Using Travis UPDATE
    export CONTEXT="TRAVIS"
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export INSTALL_DIR=${HOME}
fi

# Load the versions and directories
export CI_DIR=${SPIDER_DIR}/myci
export BUILD_DIR=$(pwd)
source $CI_DIR/versions.sh

echo "--- VARIABLES ----"

echo "Context: ${CONTEXT}"
echo "Spider Dir: ${SPIDER_DIR}"
echo "Install Dir: ${INSTALL_DIR}"
echo "CI Dir: ${CI_DIR}"
echo "NEO Dir: ${NEO4J_DIR}"
echo "Orient Dir: ${ORIENT_DIR}"
echo "Gremlin Dir: ${GREMLIN_DIR}"

echo "Neo: ${NEO4J_VERSION}"
echo "Gremlin: ${GREMLINSERVER_VERSION}"
echo "Orient: ${ORIENT_VERSION}"

echo "--- end VARIABLES ----"


if [ $CONTEXT = 'VAGRANT' ]
    then
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
fi







### JAVA
# Get dependencies (for adding repos)
sudo apt-get install -y python-software-properties
sudo add-apt-repository -y ppa:webupd8team/java
sudo apt-get update

# install oracle jdk 8
sudo apt-get install -y oracle-java8-installer
sudo update-alternatives --auto java
sudo update-alternatives --auto javac

# Add to environment
export JAVA_HOME=/usr/lib/jvm/java-8-oracle
export JRE_HOME=/usr/lib/jvm/java-8-oracle


### GREMLIN
# Install gremlin-server
wget --no-check-certificate -O $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip https://www.apache.org/dist/incubator/tinkerpop/$GREMLINSERVER_VERSION-incubating/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip
unzip $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating-bin.zip -d $INSTALL_DIR/

# get gremlin-server configuration files
cp ./myci/gremlin-spider-script.groovy $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/scripts/
cp ./myci/gremlin-server-spider.yaml $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating/conf/

# get neo4j dependencies
cd $INSTALL_DIR/apache-gremlin-server-$GREMLINSERVER_VERSION-incubating
bin/gremlin-server.sh -i org.apache.tinkerpop neo4j-gremlin $GREMLINSERVER_VERSION-incubating

# Start gremlin-server in the background and wait for it to be available
bin/gremlin-server.sh conf/gremlin-server-spider.yaml > /dev/null 2>&1 &
cd $BUILD_DIR
sleep 30


### NEO4J
# install Neo4j locally:
wget -O $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz dist.neo4j.org/neo4j-community-$NEO4J_VERSION-unix.tar.gz
tar -xzf $INSTALL_DIR/neo4j-community-$NEO4J_VERSION-unix.tar.gz -C $INSTALL_DIR/
$INSTALL_DIR/neo4j-community-$NEO4J_VERSION/bin/neo4j start
sleep 10

# changing password:
curl -vX POST http://neo4j:neo4j@localhost:7474/user/neo4j/password -d"password=j4oen"

### ORIENT
# Download orient
wget -O $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz wget http://www.orientechnologies.com/download.php?file=orientdb-community-$ORIENT_VERSION.tar.gz
tar -xzf $INSTALL_DIR/orientdb-community-$ORIENT_VERSION.tar.gz -C $INSTALL_DIR/

#update config with correct user/password
sed -i '/<users>/a <user name="root" password="root" resources="*"><\/user>' $INSTALL_DIR/orientdb-community-$ORIENT_VERSION/config/orientdb-server-config.xml

# run and wait for it to init
$INSTALL_DIR/orientdb-community-$ORIENT_VERSION/bin/server.sh > /dev/null 2>&1 &
sleep 15