#touch .htaccess
#echo 'RewriteEngine On
#RewriteCond %{REQUEST_FILENAME} !-f
#RewriteRule ^ index.php [QSA,L]' > .htaccess

wget https://getcomposer.org/download/1.2.4/composer.phar
php composer.phar install

php composer.phar dump-autoload