# Usar una imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar el contenido de core y assets a /var/www/html/
COPY src/modules/core/ /var/www/html/
COPY src/assets/ /var/www/html/assets/

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
