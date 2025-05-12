FROM php:8.1-fpm

ARG user=crater
ARG uid=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libmagickwand-dev \
    mariadb-client && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar y habilitar imagick
RUN pecl install imagick && docker-php-ext-enable imagick

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# Copiar Composer desde imagen oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario del sistema
RUN useradd -G www-data,root -u $uid -d /home/$user $user && \
    mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Establecer el directorio de trabajo
WORKDIR /var/www

# Copiar código del proyecto
COPY . .

# Instalar dependencias PHP con Composer
RUN composer install

# Asignar permisos correctos
RUN chown -R www-data:www-data storage bootstrap/cache

# Usar el nuevo usuario
USER $user

# Exponer el puerto 8080 (Render lo espera por defecto)
EXPOSE 8080

# Iniciar el servidor embebido de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]