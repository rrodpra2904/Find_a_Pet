# Uso la imagen oficial de PHP con Apache
FROM php:8.2-apache

# 1. Instalación de extensiones
# Instalo mysqli (por si acaso me hace falta utilizarlo en algún momento del proyecto de DAW) y pdo/pdo_mysql (esto lo hago para que pueda utilizar el PDO, que lo voy a 
# utilizar para que no me puedan hacer inyecciones de SQL).
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# CONFIGURACIÓN DE SUBIDA DE IMÁGENES
# Uso el archivo de configuración por defecto de desarrollo que trae la imagen
# y le inyecto los nuevos límites de 20 Megabytes para que no me eche de la sesión.
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = 8M/post_max_size = 20M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' "$PHP_INI_DIR/php.ini"

# 2. Carpeta de trabajo
# Aquí es donde Docker va a guardar y leer todos mis archivos de la página web.
WORKDIR /var/www/html

# 3. Puerto de salida
# Abro el puerto 80 para que se pueda entrar a la página web desde el navegador con el protocolo http.
EXPOSE 80