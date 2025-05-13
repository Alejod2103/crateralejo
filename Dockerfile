FROM php:8.1-fpm

ARG user=crater
ARG uid=1000

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    libzip-dev libmagickwand-dev mariadb-client && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN pecl install imagick && docker-php-ext-enable imagick
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Código de la app
WORKDIR /var/www
COPY . .

# Instalar dependencias
RUN composer install

# Ejecutar migraciones y seeders automáticamente
RUN php artisan migrate --force && php artisan db:seed

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

# Usar usuario no root
USER $user

# Servir Laravel en puerto 8080 (Render)
EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]