#!/bin/bash
# Setup Directories and Versions
if [ -n "${TRAVIS_BUILD_DIR}" ]; then
    # We are using travis and must set the directories
    export SPIDER_DIR=${TRAVIS_BUILD_DIR}
    export BUILD_DIR=${TRAVIS_BUILD_DIR}
fi
export CI_DIR = ${SPIDER_DIR}/CI
source ${CI_DIR}/versions.sh

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
