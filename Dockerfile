# Usar una imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar la carpeta core (para index.html)
COPY src/modules/core/ /var/www/html/

# Copiar assets (para imágenes y CSS)
COPY src/assets/ /var/www/html/assets/

# Copiar la carpeta php (para archivos backend)
COPY src/php/ /var/www/html/php/

# Copiar la carpeta login (si el login usa archivos específicos)
COPY src/login/ /var/www/html/login/

# Copiar cualquier otra carpeta necesaria
COPY src/coreadmin/ /var/www/html/coreadmin/
COPY src/coreDoctores/ /var/www/html/coreDoctores/
COPY src/coreVariable/ /var/www/html/coreVariable/
COPY src/citas/ /var/www/html/citas/

# Asegurar que Apache cargue index.html correctamente
RUN echo "DirectoryIndex index.html" >> /etc/apache2/apache2.conf

# Ajustar permisos para evitar problemas de acceso
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html

# Habilitar mod_rewrite si usas .htaccess
RUN a2enmod rewrite

# Exponer el puerto 80
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]
