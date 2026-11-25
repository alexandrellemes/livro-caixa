#FROM php:8.2-apache
FROM debian:12

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=8.2
ENV PHP_DIR /etc/php

# Update version
RUN apt update
RUN apt upgrade -y

# Utilities...
RUN apt install -y curl apt-transport-https lsb-release ca-certificates wget
RUN apt install -y apt-utils software-properties-common zip unzip vim lsof net-tools htop curl
RUN apt install -y wget git ssh iputils-ping bash-completion build-essential gcc gcc+ make libaio1
RUN apt install -y telnet lynx libfontconfig1 libxrender1 vim --fix-missing
RUN apt install -y net-tools tcpdump rsync dos2unix

# Debian stretch based container
RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list
RUN apt update

RUN apt install -y php8.2 php8.2-cli php8.2-dev php8.2-xml php8.2-soap php8.2-curl php8.2-gd php8.2-mbstring                php8.2-zip php8.2-opcache php8.2-common php8.2-ldap php8.2-intl
RUN apt install -y php8.2-bcmath php8.2-curl php8.2-pgsql php8.2-mysql php8.2-memcached php8.2-memcache php8.2-odbc php8.2-sqlite3

RUN apt install -y libmcrypt-dev mariadb-client libmariadb-dev sqlite3
RUN pecl channel-update pecl.php.net
#RUN a2dismod php7.4
#RUN a2dismod php8.0
#RUN a2dismod php8.1
#RUN a2dismod php8.2
#RUN a2dismod php8.3

# Habilita o módulo PHP no apache2
RUN a2enmod php${PHP_VERSION}

RUN update-alternatives --set php /usr/bin/php${PHP_VERSION}
RUN update-alternatives --set phar /usr/bin/phar${PHP_VERSION}
RUN update-alternatives --set phar.phar /usr/bin/phar.phar${PHP_VERSION}
RUN update-alternatives --set phpize /usr/bin/phpize${PHP_VERSION}
RUN update-alternatives --set php-config /usr/bin/php-config${PHP_VERSION}

#Install mcrypt for PHP >= 7.2 and <= 8.0.0
#RUN yes '' | pecl install mcrypt-1.0.6

# Configura o ServerName para o apache2
RUN echo "ServerName 127.0.0.1" >> /etc/apache2/apache2.conf

# Enable Apache2 modules
RUN a2enmod rewrite

# Habilita o POST 80MB
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/post_max_size = 8M/post_max_size = 256M/g' {} \;

# Habilita upload de arquivos com 20MB
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/upload_max_filesize = 2M/upload_max_filesize = 256M/g' {} \;

# Habilita as mensagens de erros do PHP
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/display_errors = Off/display_errors = On/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/display_startup_errors = Off/display_startup_errors = On/g' {} \;

# Define o TIMEZONE para America/Sao_Paulo
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/;date.timezone =/date.timezone = America\/Sao_Paulo/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/max_execution_time = 30/max_execution_time = 600/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/memory_limit = 128/memory_limit = 5000/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/;max_input_vars/max_input_vars/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/max_input_vars = 1000/max_input_vars = 2000/g' {} \;

# JIT enable
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/;opcache.enable=1/opcache.enable=1/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e 's/;opcache.enable_cli=0/opcache.enable_cli=1/g' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e '$aopcache.jit_buffer_size=256M' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e '$aopcache.jit=1235' {} \;
RUN find ${PHP_DIR} -name php.ini -exec sed -i -e '$aopcache.jit_debug=0' {} \;

# Set up the Apache2 environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Instala o composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer selfupdate

#RUN export PATH=${PATH}:~/.composer/vendor/bin

# Define o timezone.
ENV TZ 'America/Sao_Paulo'
RUN echo $TZ > /etc/timezone && \
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    apt-get update && apt-get install -y tzdata && \
    dpkg-reconfigure -f noninteractive tzdata

#Install Java
RUN apt install -y default-jre

# Habilita mod_ssl e mod_rewrite no Apache
RUN a2enmod ssl rewrite

# Gerar certificados autoassinados
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/localhost.key \
  -out /etc/ssl/certs/localhost.crt \
  -subj "/CN=localhost"

COPY ./localhost.conf /etc/apache2/sites-enabled/000-default.conf

# Reinicializa os servicos
#RUN service apache2 reload

WORKDIR /var/www/html

EXPOSE 80 443

# Run Apache2 in Foreground
CMD /usr/sbin/apache2ctl -D FOREGROUND
