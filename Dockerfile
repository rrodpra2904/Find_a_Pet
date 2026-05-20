# Uso la imagen oficial de PHP con Apache

FROM php:8.2-apache

# 1. Instalación de extensiones

# Instalo mysqli (por si acaso me hace falta utilizarlo en algún momento del proyecto de DAW) y pdo/pdo_mysql (esto lo hago para que pueda utilizar el PDO, que lo voy a 
# utilizar para que no me puedan hacer inyecciones de SQL).

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# 2. Carpeta de trabajo

# Aquí es donde Docker va a guardar y leer todos mis archivos de la página web.

WORKDIR /var/www/html

# 3. Puerto de salida

# Abro el puerto 80 para que se pueda entrar a la página web desde el navegador con el protocolo http.

EXPOSE 80
