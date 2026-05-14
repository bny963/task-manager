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

---

## データベース設計 (ER図)

```mermaid
erDiagram
    users ||--o{ tasks : "作成する"
    statuses ||--o{ tasks : "状態を定義する"

    users {
        bigint id PK "プライマリキー"
        string name "ユーザー名"
        string email "メールアドレス"
        string password "パスワード"
        timestamp created_at
    }

    tasks {
        bigint id PK "プライマリキー"
        bigint user_id FK "作成者ID"
        bigint status_id FK "ステータスID"
        string title "タスク名"
        text description "詳細"
        datetime due_date "期限"
    }

    statuses {
        bigint id PK "プライマリキー"
        string name "Todo / Doing / Done"
    }