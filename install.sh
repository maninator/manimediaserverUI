#!/bin/bash

####################
##     Global     ##
####################

CWD=$PWD

# Where are we installing Mani's files
INSTALL_DIR="${INSTALL_DIR:-$CWD}" # Decide where this will be installed
INSTALL_DIR="${INSTALL_DIR%/}" # Remove trailing slash if one was added
INSTALL_IS_SAME_DIR="false"
if [[ "$CWD" == "$INSTALL_DIR" ]]; then
    INSTALL_IS_SAME_DIR="true"
fi

# Where is media located
MEDIA_DIR="${MEDIA_DIR:-$INSTALL_DIR/media}"
DOWNLOADS_DIR="${DOWNLOADS_DIR:-$INSTALL_DIR/media/Downloads}"

# Default timezone
DEFAULT_TZ="${DEFAULT_TZ:-Europe/Amsterdam}"

# Set the port you wish the Mani service to be run on
SERVICE_PORT="${SERVICE_PORT:-3626}"

#######################
##     Functions     ##
#######################

function check_root {
    echo "##########################################"
    echo "##     Checking for root permission     ##"
    echo "##########################################"
    if [ "$(id -u)" != "0" ]; then
        echo "ERROR: This script must be run as root...."
        exit 1
    fi
}

function check_OS {
    echo "#######################################"
    echo "##     Checking operating system     ##"
    echo "#######################################"
    OS_MACHINE=`uname -m`
    if [ "$OS_MACHINE" != "x86_64" ]; then
        echo "ERROR: This script only supports 64-bit operating systems...."
        exit 1
    fi

    OS_NUMBER=`cat /etc/lsb-release | grep RELEASE | awk -F "=" '{print $2}'`
    if [ "$OS_NUMBER" != "16.04" ] && [ "$OS_NUMBER" != "17.04" ]  && [ "$OS_NUMBER" != "2" ]; then
        echo "ERROR: This script only supports Ubuntu 16.04 (Trusty Tahr), Ubuntu 17.04 (Zesty) and LMDE 2.0 (Betsy)...."
        exit 1
    fi
}


### Dependencies
function install_deps {
    apt-get install -y git python-pip python3-pip sendmail
    pip2 install --upgrade pip
    pip2 install docker-py configparser
    pip3 install --upgrade pip
    pip3 install docker-py configparser
}


### Environment
function setup_env {
    # Setup config folder
    mkdir -p ${INSTALL_DIR}/config
    if [ ! -f ${INSTALL_DIR}/config/config.ini ]; then
        cp -fv ${CWD}/setup/config/config.ini ${INSTALL_DIR}/config/config.ini
    fi
    sed -i 's#{BASE_DIR}#'${INSTALL_DIR}'#g' ${INSTALL_DIR}/config/config.ini
    sed -i 's#{MEDIA_DIR}#'${MEDIA_DIR}'#g' ${INSTALL_DIR}/config/config.ini
    sed -i 's#{DOWNLOADS_DIR}#'${DOWNLOADS_DIR}'#g' ${INSTALL_DIR}/config/config.ini
    sed -i 's#{DEFAULT_TZ}#'${DEFAULT_TZ}'#g' ${INSTALL_DIR}/config/config.ini
    sed -i 's#{SERVICE_PORT}#'${SERVICE_PORT}'#g' ${INSTALL_DIR}/config/config.ini
    chown -R www-data:www-data ${INSTALL_DIR}/config/config.ini
    find ${INSTALL_DIR}/config -type d -exec sudo chmod 775 -f {} \;

    # Setup users media folders for their personalised libraries
    mkdir -p ${INSTALL_DIR}/libraries
    sed -i 's#{LIBRARIES}#'${INSTALL_DIR}/libraries'#g' ${INSTALL_DIR}/config/config.ini
    chown -Rv www-data:www-data ${INSTALL_DIR}/libraries
    find ${INSTALL_DIR}/libraries -type d -exec sudo chmod 775 -f {} \;

    # Install Mani libs
    cp -rfv ${CWD}/setup/lib ${INSTALL_DIR}/
    chown -R www-data:www-data ${INSTALL_DIR}/lib
    find ${INSTALL_DIR}/lib -type d -exec sudo chmod 775 -f {} \;

    # Install Nginx configuration
    cp -rfv ${CWD}/setup/assets/nginx_settings ${INSTALL_DIR}/
    chown -R www-data:www-data ${INSTALL_DIR}/nginx_settings
    find ${INSTALL_DIR}/nginx_settings -type d -exec sudo chmod 775 -f {} \;
    find ${INSTALL_DIR}/nginx_settings -type f -exec sudo chmod 664 -f {} \;
    mkdir -p /etc/nginx/
    rm -rfv /etc/nginx/sites-available
    ln -s ${INSTALL_DIR}/nginx_settings/sites-available /etc/nginx/sites-available
    rm -rfv /etc/nginx/mani-snippets
    ln -s ${INSTALL_DIR}/nginx_settings/mani-snippets /etc/nginx/mani-snippets
    mkdir -p /var/www/html
    chown -R www-data:www-data ${CWD}/www
    chmod 775 ${CWD}/www
    find ${CWD}/www -type d -exec sudo chmod 775 -f {} \;
    find ${CWD}/www -type f -exec sudo chmod 664 -f {} \;
    rm -rfv /var/www/html/mani
    ln -s ${CWD}/www /var/www/html/mani

    # Install Mani bin
    rm -fv /usr/bin/mani
    chmod +x ${INSTALL_DIR}/lib/mani_main.py
    ln -s ${INSTALL_DIR}/lib/mani_main.py /usr/bin/mani

    # Install systemd service
    cp -fv setup/assets/systemd/mani.service /etc/systemd/system/mani.service
    systemctl daemon-reload
}


### Docker
# https://zaiste.net/posts/removing_docker_containers/
function install_docker {
    apt-get install -y apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    if ! grep ^ /etc/apt/sources.list | grep docker; then
        add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
        apt-get update
    fi
    apt-get install -y docker-ce
    pip install docker-py
}


### PHP
function install_php {
    apt-get install -y php-fpm php-mysql php-gd php-intl php-curl php-mbstring php-xml
    systemctl restart php7.0-fpm
}


### Mysql
function install_mysql {
    apt-get install -y mysql-server
    mysql_secure_installation
    echo "Do you need to setup MYSQL with a new database, user and password for this install? [y/n]"
    read AN
    if [ "$AN" == "y" ]; then
        echo "Enter your root password:"
        read ROOT
        echo "Enter a password for this new user and database:"
        read AN
        if [ "$AN" != "" ]; then
            cp -fv $CWD/setup/mani.sql /tmp/mani.sql
            sed -i 's/{!PASSWORD}/'$AN'/g' /tmp/mani.sql
            mysql -u root --password=$ROOT < /tmp/mani.sql
        fi
    fi
}


### Nginx stuff
function install_nginx {
    apt-get install -y nginx apache2-utils
    # chown -vR :www-data /var/www/html/
    # chmod -vR g+w /var/www/html/
    adduser mani
    adduser mani www-data
    useradd -G www-data mani
    usermod -aG sudo username
    # sudo htpasswd -c /etc/nginx/.htpasswd mani
}

function install_nginx_config {
    if [ ! -f /etc/nginx/sites-available/mani ]; then 
        echo "Installing Mani Nginx config"
        cp -rfv nginx_settings/* /etc/nginx/
        cd /etc/nginx/sites-enabled
        rm -rfv ./*
        ln -s /etc/nginx/sites-available/mani
        service nginx restart
    fi
}


### Install apps
# Emby
function install_emby {
    mkdir -p ${INSTALL_DIR}/emby/config
    docker pull emby/embyserver
    docker create --name=embyserver \
        --net=host \
        -v ${INSTALL_DIR}/emby/config:/config \
        -v /mnt:/mnt \
        -e TZ=${DEFAULT_TZ} \
        emby/embyserver
}
function run_emby {
    mkdir -p ${INSTALL_DIR}/emby/config
    docker pull emby/embyserver
    docker start embyserver
}


### Transmission
function install_transmission {
    mkdir -p ${INSTALL_DIR}/transmission/config
    ln -s ${DOWNLOADS_DIR}/ ${INSTALL_DIR}/transmission/downloads 
    docker pull linuxserver/transmission
    docker create --name=transmission \
        -v ${INSTALL_DIR}/transmission/config:/config \
        -v ${INSTALL_DIR}/transmission/downloads:/downloads \
        -v ${INSTALL_DIR}/transmission/watch:/watch \
        -e PGID=1001 -e PUID=1001 \
        -e TZ=${DEFAULT_TZ} \
        -p 9091:9091 -p 51413:51413 \
        -p 51413:51413/udp \
        linuxserver/transmission
}


### SickRage
function install_sickrage {
    mkdir -p ${TVSHOWS_DIR}
    mkdir -p ${MOVIES_DIR}
    mkdir -p ${INSTALL_DIR}/sickrage/config
    ln -s ${DOWNLOADS_DIR}/ ${INSTALL_DIR}/sickrage/downloads 
    docker pull linuxserver/sickrage
    docker create --name=sickrage \
        -v ${INSTALL_DIR}/sickrage/config:/config \
        -v ${INSTALL_DIR}/sickrage/downloads:/downloads \
        -v ${TVSHOWS_DIR}:/tv2 \
        -e PGID=1001 -e PUID=1001  \
        -e TZ=${DEFAULT_TZ} \
        --net=host \
        linuxserver/sickrage
}


### Ombi
function install_ombi {
    apt-get update
    apt-get install -y mono-complete unzip
}


function continue_check {
    check_root
    check_OS
}

function setup_all {
    echo "This script will install and configure Mani the Media Server, This script only supports Ubuntu 16.04 (Trusty Tahr) 64-bit...." 
    apt-get update
    install_deps
    install_docker
    install_nginx
    install_nginx_config
    install_php
    install_mysql
}


#####################
##     Execute     ##
#####################

if [ "$1" == "all" ]; then
    continue_check
    setup_all
elif [ "$1" == "deps" ]; then
    apt-get update
    install_deps
elif [ "$1" == "env" ]; then
    continue_check
    setup_env
elif [ "$1" == "run" ]; then
    sudo mount -a
    sudo service mani start
    mani start embyserver transmission sickrage couchpotato ombi
else
    echo "Try running '<SCRIPT NAME> all'"
fi

exit 0
