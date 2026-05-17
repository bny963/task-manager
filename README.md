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

![ER図](https://github.com/user-attachments/assets/c97cc032-b428-41e3-b2ba-6129fa263a69)

### リレーションの補足
* **users と tasks**: 1人のユーザーが「作成者 (`user_id`)」および「担当者 (`assigned_to`)」として、複数のタスクに紐付きます。※担当者は未割り当て（nullable）を許容する設計です。
* **categories と tasks**: 1つのカテゴリに対して、複数のタスクが分類されます。
