#!/bin/bash

# Missä kansiossa komento suoritetaan
DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

source $DIR/config/environment.sh

echo "Luodaan css..."

bash compilescss.sh

echo "Siirretään tiedostot users-palvelimelle..."

# Tämä komento siirtää tiedostot palvelimelta
rsync -z -r -e "ssh -p $PORT" $DIR/app $DIR/assets $DIR/config $DIR/lib $DIR/sql $DIR/vendor $DIR/index.php $DIR/composer.json $USERNAME@$SERVER:$REMOTE_DIR/$PROJECT_FOLDER

echo "Valmis!"

echo "Suoritetaan komento php composer.phar dump-autoload..."

# Suoritetaan php composer.phar dump-autoload
ssh $USERNAME@$SERVER -p $PORT "
cd $REMOTE_DIR/$PROJECT_FOLDER
php composer.phar dump-autoload
exit"

echo "Valmis!"
