# =============================================
# 「ごめんの前に」を Render で動かすための Dockerfile
# PHP + Apache に、PostgreSQL 用の拡張を入れる
# =============================================
FROM php:8.3-apache

# PostgreSQL に接続するための拡張（pdo_pgsql）を入れる
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*

# 本番用の PHP 設定（エラーの中身を画面に出さない）
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Render は 10000 番ポートにアクセスしてくるので、Apache をそのポートで待ち受けさせる
ENV PORT=10000
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# 設定ファイルや初期データはブラウザから見られないようにする
RUN printf '<FilesMatch "(^Dockerfile$|\\.sql$|\\.yaml$|\\.md$)">\n    Require all denied\n</FilesMatch>\n' > /etc/apache2/conf-enabled/deny-files.conf

# アプリのファイルを全部コピー
COPY . /var/www/html/
