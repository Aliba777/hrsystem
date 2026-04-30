FROM php:8.2-apache

# Install PHP extensions and system packages
RUN apt-get update && apt-get install -y \
    cron \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Create log directory
RUN mkdir -p /var/log && chmod 755 /var/log

# Copy cron configuration
COPY config/crontab /etc/cron.d/hr-connect-cron
RUN chmod 0644 /etc/cron.d/hr-connect-cron && \
    crontab /etc/cron.d/hr-connect-cron

# Start cron and Apache
CMD cron && apache2-foreground

EXPOSE 80
