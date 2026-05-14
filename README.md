# 環境構築

## Dockerビルド
- `git clone https://github.com/bny963/task-manager.git`
- `./vendor/bin/sail up -d`

## Laravel環境構築 (Sail)
- `./vendor/bin/sail composer install`
- `cp .env.example .env` 、環境変数を変更
- `./vendor/bin/sail php artisan key:generate`
- `./vendor/bin/sail php artisan migrate`
- `./vendor/bin/sail php artisan db:seed`
- `./vendor/bin/sail npm install`
- `./vendor/bin/sail npm run dev`

---

## 開発環境
- お問い合わせ画面：[http://localhost/](http://localhost/)
- ユーザー登録：[http://localhost/register](http://localhost/register)
- phpMyAdmin：[http://localhost:8080/](http://localhost:8080/)

---

## 使用技術(実行環境)
- PHP 8.x
- Laravel (Sail)
- Docker / Docker Compose
- MySQL 8.0
- Node.js / npm (Vite)