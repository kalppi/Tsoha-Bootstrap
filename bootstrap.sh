#!/bin/bash

source config/environment.sh

echo "Luodaan projektikansio..."

# Luodaan projektin kansio
ssh $USERNAME@$SERVER -p $PORT "
cd $REMOTE_DIR
touch favicon.ico
mkdir $PROJECT_FOLDER
cd $PROJECT_FOLDER
touch .htaccess
echo 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]' > .htaccess
exit"

echo "Valmis!"

echo "Siirretään tiedostot users-palvelimelle..."

# Siirretään tiedostot palvelimelle
scp -r -P $PORT app config lib vendor sql assets index.php composer.json $USERNAME@$SERVER:$REMOTE_DIR/$PROJECT_FOLDER

echo "Valmis!"

echo "Asetetaan käyttöoikeudet ja asennetaan Composer..."

# Asetetaan oikeudet ja asennetaan Composer
ssh $USERNAME@$SERVER -p $PORT "
chmod -R a+rX htdocs
cd $REMOTE_DIR/$PROJECT_FOLDER
wget https://getcomposer.org/download/1.2.4/composer.phar
php composer.phar install
exit"

echo "Valmis!"
