FROM php:latest

ADD php/_010_php.ini /usr/local/etc/php/conf.d/

RUN apt-get update && apt-get install --no-install-recommends -y \
        libmcrypt-dev \
        zip \
        zlib1g-dev \
        unzip \
        wget \
        curl \
        libcurl4-gnutls-dev \
        libxml2 \
        libxml2-dev \
        netcat \
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install zip \
    && docker-php-ext-install curl

RUN echo "date.timezone = 'Europe/Berlin'" >> /usr/local/etc/php/php.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN mv composer.phar /usr/local/bin/composer

COPY ./wait.sh /wait.sh
RUN chmod +x /wait.sh

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

#RUN useradd dev
#RUN mkdir -p  /home/dev/tests
#ENV HOME /home/dev
#
#RUN chown -R dev: /home/dev
#USER dev
#
#WORKDIR /home/dev/tests
WORKDIR /tests

CMD ["php" "-a"]