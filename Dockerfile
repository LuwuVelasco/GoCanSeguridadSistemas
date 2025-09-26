# Dockerfile
FROM php:8.2-apache

# Instalar extensiones requeridas para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql pgsql \
 && rm -rf /var/lib/apt/lists/*

# Copiar código al docroot
COPY src/modules/core/ /var/www/html/
COPY src/assets/ /var/www/html/assets/
COPY src/modules/php/ /var/www/html/php/
COPY src/modules/login/ /var/www/html/login/
COPY src/modules/coreadmin/ /var/www/html/coreadmin/
COPY src/modules/coreDoctores/ /var/www/html/coreDoctores/
COPY src/modules/coreVariable/ /var/www/html/coreVariable/
COPY src/modules/citas/ /var/www/html/citas/

# Asegurar index.html como página por defecto
RUN echo "DirectoryIndex index.html" >> /etc/apache2/apache2.conf

# Activar mod_rewrite (si en algún momento lo usas)
RUN a2enmod rewrite

# Permisos
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Render mapea el puerto por ti; Apache escucha en 80
EXPOSE 80
CMD ["apache2-foreground"]