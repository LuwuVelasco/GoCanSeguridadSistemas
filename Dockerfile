FROM php:8.2-apache

# Copiar carpeta core al docroot
COPY src/modules/core/ /var/www/html/

# Copiar carpeta assets
COPY src/assets/ /var/www/html/assets/

# Copiar carpeta php
COPY src/modules/php/ /var/www/html/php/

# Copiar carpeta login
COPY src/modules/login/ /var/www/html/login/

# Copiar carpeta coreadmin
COPY src/modules/coreadmin/ /var/www/html/coreadmin/

# Copiar carpeta coreDoctores
COPY src/modules/coreDoctores/ /var/www/html/coreDoctores/

# Copiar carpeta coreVariable
COPY src/modules/coreVariable/ /var/www/html/coreVariable/

# Copiar carpeta citas
COPY src/modules/citas/ /var/www/html/citas/

# Asegurar que Apache cargue index.html
RUN echo "DirectoryIndex index.html" >> /etc/apache2/apache2.conf

# Ajustar permisos
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html

# Activar mod_rewrite (si lo usas)
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
