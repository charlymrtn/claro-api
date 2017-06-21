#!/bin/sh

# Provision Claro Pay apps
if [ -d /home/vagrant/claropay/admin ]; then
	echo 'Provisioning Claro Pay Admin'
    cd /home/vagrant/claropay/admin
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi

if [ -d /home/vagrant/claropay/api ]; then
	echo 'Provisioning Claro Pay API'
    cd /home/vagrant/claropay/api
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi

if [ -d /home/vagrant/claropay/clientes ]; then
	echo 'Provisioning Claro Pay Clientes'
    cd /home/vagrant/claropay/clientes
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi

if [ -d /home/vagrant/claropay/boveda ]; then
	echo 'Provisioning Claro Pay Boveda'
    cd /home/vagrant/claropay/boveda
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi

if [ -d /home/vagrant/claropay/queue ]; then
	echo 'Provisioning Claro Pay Queue'
    cd /home/vagrant/claropay/queue
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi

if [ -d /home/vagrant/claropay/monitor ]; then
	echo 'Provisioning Claro Pay Monitor'
    cd /home/vagrant/claropay/monitor
    composer install
    composer dump-autoload
    php artisan migrate --seed
    php artisan key:generate
fi
