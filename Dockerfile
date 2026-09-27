FROM php:8.5.11-apache-trixie

WORKDIR /var/www

COPY www/* html/
COPY inc inc/

COPY cfg/config.inc.php.dist cfg/config.inc.php
RUN sed -i "s#'upload_directory' =>.*#'upload_directory' => '/var/www/html/files',#" cfg/config.inc.php && \
    sed -i "s#'upload_server' =>.*#'upload_server' => \$_ENV[\"UPLOAD_SERVER\"],#" cfg/config.inc.php && \
    sed -i "s#'base_server' =>.*#'base_server' => \$_ENV[\"BASE_SERVER\"],#" cfg/config.inc.php && \
    sed -i "s#'contact_email' =>.*#'contact_email' => \$_ENV[\"CONTACT_EMAIL\"],#" cfg/config.inc.php && \
    sed -i "s#'password' =>.*#'password' => \$_ENV[\"PASSWORD\"] ?? NULL,#" cfg/config.inc.php && \
    mkdir /var/www/html/files && \
    chown www-data:www-data /var/www/html/files

RUN a2enmod actions && \
    sed -i '/<\/VirtualHost>/i\\tScript PUT /put.php' /etc/apache2/sites-available/000-default.conf
