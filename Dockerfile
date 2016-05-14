FROM ubuntu:15.10

RUN apt-get update
RUN apt-get install software-properties-common python-software-properties -y #> /dev/null

RUN yes | apt-get install php5
RUN yes | apt-get install php5-xdebug
RUN apt-get install curl php5-curl php5-gd php5-mcrypt -y #> /dev/null

RUN curl --silent https://getcomposer.org/installer | php #> /dev/null 2>&1
RUN mv composer.phar /usr/local/bin/composer
