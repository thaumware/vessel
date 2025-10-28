FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias Laravel
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Generar key si no existe
RUN php artisan key:generate --force || true

# Exponer puerto
EXPOSE 8000

# Comando por defecto (sobrescrito en compose si es necesario)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]