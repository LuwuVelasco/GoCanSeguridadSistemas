# Usar una imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar SOLO el contenido de 'src/core' a /var/www/html/
COPY src/core/ /var/www/html/

# Asegurar que Apache cargue el archivo index.html correctamente
RUN echo "DirectoryIndex index.html" >> /etc/apache2/apache2.conf

# Establecer permisos correctos
RUN chmod -R 755 /var/www/html && chown -R www-data:www-data /var/www/html

# Habilitar módulo de reescritura en Apache (si usas .htaccess)
RUN a2enmod rewrite

# Exponer el puerto 80
EXPOSE 80

# Iniciar Apache en primer plano
CMD ["apache2-foreground"]
