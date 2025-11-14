FROM php:8.2-fpm-alpine

# Define o diretório de trabalho padrão (pasta de arquivos do FPM)
WORKDIR /var/www/html

# Instala extensões PHP e dependências
# 1. Instala as dependências de build (para algumas extensões)
# 2. Instala as extensões PHP (os pacotes 'php82-...')
RUN apk update && apk add --no-cache \
    git \
    php82-pdo \
    php82-pdo_mysql \
    php82-mysqli \
    php82-opcache \
    php82-session \
    php82-json \
    php82-mbstring \
    php82-xml \
    composer \
    && rm -rf /var/cache/apk/*


RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli 
# Instalar o composer como um pacote separado (mais limpo no Alpine)
# Se o pacote 'composer' já estiver na sua lista de 'apk add', esta linha é redundante, 
# mas garante que o composer esteja disponível globalmente.
# RUN apk add --no-cache composer

COPY custom.ini /usr/local/etc/php/conf.d/

# Corrige o erro: O usuário padrão do PHP-FPM no Alpine é 'www-data'.
# Garantimos que ele tenha permissão para acessar o volume montado.
RUN chown -R www-data:www-data /var/www/html

# Expor a porta 9000 (padrão do PHP-FPM)
# EXPOSE 9000