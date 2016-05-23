#!/usr/bin/env bash

# https://github.com/cweagans/mcrypt-polyfill/blob/master/util.sh#L9
requireDocker() {
    docker ps > /dev/null 2>&1
    if [[ "$?" != "0" ]]; then
        cat <<EOT
To continue, you must have a working Docker installation. If you don't have this
set up, install Docker Toolbox and follow the tutorial for docker-machine. When
you can successfully run "docker ps", you can re-run this script.
EOT
        exit 1
    fi
}