#!/usr/bin/env bash
sudo apt-get install -y php5-mcrypt;
sudo php5enmod mcrypt;
sudo rm -R vendor;
sudo rm -R node_modules;
php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php
php -r "if (hash('SHA384', file_get_contents('composer-setup.php')) === 'fd26ce67e3b237fffd5e5544b45b0d92c41a4afe3e3f778e942e43ce6be197b9cdc7c251dcde6e2a52297ea269370680') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo npm install -g n;
sudo n latest;
echo "installing bower ...";
sudo npm install -g bower;
echo "installing composer dependencies ...";
sudo rm $PWD/bootstrap/environment.php;
touch $PWD/bootstrap/environment.php;
echo "<?php \$env = \$app->detectEnvironment(array('local'=> array('$HOSTNAME')));" >>  $PWD/bootstrap/environment.php;
php composer.phar install --prefer-dist;
php composer.phar dump-autoload --optimize;
sudo chmod 775 -R $PWD/app/storage;
if [ -f "package.json" ]; then
    sudo npm install;
fi
if [ -f "bower.json" ]; then
    bower install --allow-root --config.interactive=false;
fi
php artisan migrate --env=local;