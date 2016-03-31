#!/bin/bash

#################### ONLY FOR NOW #####################
# Load the Vagrant context if there is none present
if [ -z ${TRAVIS_BUILD_DIR+x} ]
    then
    # Using vagrant
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
#################### ONLY FOR NOW #####################


sudo apt-get update
apt-get install software-properties-common python-software-properties -y
sudo add-apt-repository -y ppa:webupd8team/java
sudo apt-get update

## Install oracle jdk 8
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



######## OLD TRAVIS WAY ##############
## Get dependencies (for adding repos)
#sudo apt-get install -y python-software-properties
#sudo add-apt-repository -y ppa:webupd8team/java
#sudo apt-get update
#
## install oracle jdk 8
#sudo apt-get install -y oracle-java8-installer
#sudo update-alternatives --auto java
#sudo update-alternatives --auto javac
#
## Add to environment
#export JAVA_HOME=/usr/lib/jvm/java-8-oracle
#export JRE_HOME=/usr/lib/jvm/java-8-oracle
