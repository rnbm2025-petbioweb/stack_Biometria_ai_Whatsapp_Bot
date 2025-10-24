# =========================================================
# 🐳 PETBIO11 - Landing + Formularios
# PHP 8.2 + Nginx + Seguridad + Logs (para Render)
# =========================================================

FROM php:8.2-fpm

RUN apt-get update && \
#    apt-get install -y nginx nano bash curl unzip && \
    apt-get install -y nginx supervisor nano bash curl unzip && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

#COPY nginx.conf /etc/nginx/nginx.conf
COPY petbio_landing/nginx.conf /etc/nginx/nginx.conf
        
        
COPY security-headers.conf /etc/nginx/security-headers.conf
COPY conf.d/ /etc/nginx/conf.d/
COPY . /var/www/html/
COPY deploy/supervisord.conf /etc/supervisor/conf.d/supervisord.conf


RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN { \
    echo "cgi.fix_pathinfo=0"; \
    echo "upload_max_filesize=250M"; \
    echo "post_max_size=250M"; \
    echo "max_execution_time=300"; \
} > /usr/local/etc/php/conf.d/uploads.ini

RUN mkdir -p /var/log/nginx && chmod -R 755 /var/log/nginx

EXPOSE 80

#CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

#o reemplazamos supervisor por : 

#CMD php-fpm -D && nginx -g "daemon off;"
#CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]

# Iniciar Supervisor (levanta PHP-FPM y Nginx)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

#CMD ["/usr/bin/supervisord", "-n"]
