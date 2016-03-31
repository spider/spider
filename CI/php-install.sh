#!/bin/bash

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
