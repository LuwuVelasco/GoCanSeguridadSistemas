# Usar una imagen base con PHP y Apache
FROM php:8.2-apache

# Copiar todo el contenido de tu proyecto al contenedor
COPY . /var/www/html/

# Exponer el puerto 80 (puerto predeterminado para HTTP)
EXPOSE 80

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Habilitar el módulo de Apache para reescribir URLs (si es necesario)
RUN a2enmod rewrite

# Iniciar el servidor Apache
CMD ["apache2-foreground"]