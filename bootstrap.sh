#!/bin/bash

source config/environment.sh

echo "Luodaan projektikansio..."

# Luodaan projektin kansio
ssh $USERNAME@$SERVER -p $PORT "
cd htdocs
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
scp -r -P $PORT app config lib vendor sql assets index.php composer.json $USERNAME@$SERVER:htdocs/$PROJECT_FOLDER

echo "Valmis!"

echo "Asetetaan käyttöoikeudet ja asennetaan Composer..."

# Asetetaan oikeudet ja asennetaan Composer
ssh $USERNAME@$SERVER -p $PORT "
chmod -R a+rX htdocs
cd htdocs/$PROJECT_FOLDER
wget https://getcomposer.org/download/1.2.4/composer.phar
php composer.phar install
exit"

echo "Valmis! Sovelluksesi on nyt valmiina osoitteessa $USERNAME.users.cs.helsinki.fi/$PROJECT_FOLDER"
