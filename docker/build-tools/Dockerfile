FROM php:5-cli

ADD php/_010_php.ini /usr/local/etc/php/conf.d/

RUN apt-get update && apt-get install --no-install-recommends -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng12-dev \
        zip \
        zlib1g-dev \
        unzip \
        mysql-client \
        ssmtp \
        wget \
        tar \
        libcurl4-gnutls-dev \
        curl \
        libxml2 \
        libxml2-dev \
        openssl \
        libssl-dev \
        netcat \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysqli \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install ftp \
    && docker-php-ext-install soap \
    && docker-php-ext-install curl \
    && docker-php-ext-install pdo pdo_mysql

RUN echo "date.timezone = 'Europe/Berlin'" >> /usr/local/etc/php/php.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN mv composer.phar /usr/local/bin/composer

RUN wget http://shopwarelabs.github.io/sw-cli-tools/sw.phar \
    && mv sw.phar /usr/local/bin/sw
RUN chmod +x /usr/local/bin/sw

COPY ./wait-mysql.sh /wait-mysql.sh
RUN chmod +x /wait-mysql.sh

COPY ./wait-smtp.sh /wait-smtp.sh
RUN chmod +x /wait-smtp.sh

WORKDIR /tests

CMD ["php" "-a"]