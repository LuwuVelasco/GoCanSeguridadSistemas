# Usar una imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar TODO el contenido del proyecto dentro del contenedor
COPY . /var/www/html/

# Asegurar que Apache cargue el archivo index.html correcto
RUN echo "DirectoryIndex src/core/index.html" >> /etc/apache2/apache2.conf

# Establecer permisos adecuados
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html

# Exponer el puerto 80 (para HTTP)
EXPOSE 80

# Habilitar el módulo de reescritura de Apache (si es necesario)
RUN a2enmod rewrite

# Iniciar el servidor Apache
CMD ["apache2-foreground"]
