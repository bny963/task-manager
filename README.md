# タスク管理アプリ　環境構築

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


## 開発環境（アクセスURL）
お問い合わせ画面: http://localhost/

ユーザー登録: http://localhost/register

phpMyAdmin: http://localhost:8080/

## 使用技術(実行環境)
Language: PHP 8.x

Framework: Laravel (Sail)

Infrastructure: Docker / Docker Compose

Database: MySQL 8.0

Frontend Tooling: Node.js / npm (Vite)

---

## データベース設計 (ER図)

erDiagram
    users ||--o{ tasks : "作成する"
    statuses ||--o{ tasks : "状態を定義する"

    users {
        id PK
        name
        email
        password
        created_at
    }

    tasks {
        id PK
        user_id FK
        status_id FK
        title
        description
        due_date
    }

    statuses {
        id PK
        name
    }

    statuses {
        bigint id PK "プライマリキー"
        string name "Todo / Doing / Done"
    }
