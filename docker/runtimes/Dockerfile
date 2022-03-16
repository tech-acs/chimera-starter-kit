ARG OS_VERSION=3.14
FROM alpine:${OS_VERSION}

#Working directory 
WORKDIR /var/www/html

# Install packages nginx
RUN apk add --no-cache nginx

# Install packages php

RUN apk add --no-cache \
  php8 \
  php8-fpm \
  php8-gd \
  php8-mysqli \
  php8-ctype \
  php8-curl \
  php8-dom \
  php8-intl \
  php8-json \
  php8-mbstring \
  php8-opcache \
  php8-openssl \
  php8-phar \
  php8-session \
  php8-xml \
  php8-xmlreader \
  php8-zlib \
  php8-pgsql \
  php8-gd \
  php8-pcntl \ 
  supervisor 
RUN apk add php8-dom
RUN apk add php8-fileinfo
RUN apk add php8-zip
RUN apk add php8-iconv
RUN apk add php8-tokenizer
RUN apk add php8-pdo
RUN apk add php8-pdo_pgsql
RUN apk add bash
# link php8 to php (backward compatibility)
RUN ln -s /usr/bin/php8 /usr/bin/php

#Installing composer
 RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
     php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
     php composer-setup.php && \
     php -r "unlink('composer-setup.php');" && \
     mv composer.phar /usr/local/bin/composer;



#Configure nginx php-fpm and supervisor
COPY runtimes/config/nginx.conf /etc/nginx/nginx.conf
COPY runtimes/config/php-fpm.conf /etc/php8/php-fpm.d/acs.conf
COPY runtimes/config/php.ini /etc/php8/conf.d/acs.ini
COPY runtimes/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# add user acs:acs and make owner of /var/www/html and finally switch to acs:acs
RUN addgroup -S acs 
RUN adduser -S acs -G acs

RUN mkdir -p /var/log/supervisor
RUN mkdir -p /var/log/nginx
RUN mkdir -p /var/log/php8
RUN mkdir -p /var/log/horizon
RUN mkdir -p /var/log/php

RUN touch /var/log/supervisor/supervisord.log
RUN touch /var/log/horizon/horizon.log
RUN touch /var/log/nginx/access.log
RUN touch /var/log/nginx/error.log

# COPY --from=builder /app/vendor /var/www/html/vendor


COPY composer.json composer.lock ./
RUN composer install --no-dev --no-autoloader

COPY runtimes/entrypoint.sh /usr/local/bin/start-container
RUN chown -R acs:acs /var/www/html /run /var/log/nginx /var/lib/nginx /var/log/supervisor /var/log/php8 /var/log/horizon
RUN chown -R acs:acs /usr/local/bin/start-container
RUN chown -R acs:acs /usr/local/bin/composer
RUN chmod +x /usr/local/bin/start-container
RUN mkdir /.composer
RUN chown -R acs:acs /.composer
COPY . /var/www/html/
RUN chown -R acs:acs /var/www/html 
USER acs
EXPOSE 8080

ENTRYPOINT ["start-container"]

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping