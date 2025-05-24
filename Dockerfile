FROM php:8.2-apache

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    unzip \
    git

# Installer l'extension MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Activer mod_rewrite pour Apache (utile pour MVC)
RUN a2enmod rewrite

# Copier le code source
COPY . /var/www/html

# Exclure les dossiers qui ne doivent pas être dans le conteneur
RUN rm -rf /var/www/html/Dockerfile /var/www/html/docker-compose.yml /var/www/html/data

# Donner les bonnes permissions
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html