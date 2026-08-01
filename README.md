# 🩺医師会会員専用サイト

## プロジェクトの概要
このプロジェクトは、医師会会員向けの専用サイトです。</br>
Laravel（API）と Vue（SPA）を用いたフルスタック構成で、会員向け機能と管理者向け機能を備えています。

会員はログイン後に医師会からのお知らせ、研修会情報、会員情報の確認・変更などを行うことができ、
管理者は CMS 画面からお知らせ配信、会員管理、研修会管理、ロール設定などを行えます。

Tailwind CSS によるレスポンシブ対応、Railway へのデプロイ、Mailpit を用いたメール開発環境など、
実運用を想定した構成で開発しています。

## 本番環境（Railway）
https://member-portal-production-960b.up.railway.app

## 環境構築（Laravel Sail)
  1. リポジトリをクローンし、プロジェクトフォルダに移動
``` bash
git clone git@github.com:yun-0312/member-portal.git
cd member-portal
```
 2. Sailを起動
``` bash
./vendor/bin/sail up -d
```
初回は依存関係がないので以下を実行
``` bash
composer install
```
 3. 「.env」の設定
``` text
    # データベース設定(Sailのデフォルト)
     DB_CONNECTION=mysql
     DB_HOST=mysql
     DB_PORT=3306
     DB_DATABASE=laravel
     DB_USERNAME=sail
     DB_PASSWORD=password

    # MailPit設定(Sailのデフォルト)
    MAIL_MAILER=smtp
    MAIL_HOST=mailpit
    MAIL_PORT=1025
    MAIL_USERNAME=null
    MAIL_PASSWORD=null
    MAIL_ENCRYPTION=null
    MAIL_FROM_ADDRESS="hello@example.com"
    MAIL_FROM_NAME="${APP_NAME}"

```
 4. アプリケーションキー作成
``` bash
./vendor/bin/sail artisan key:generate
```
 5. マイグレーション、シーディング実行
``` bash
./vendor/bin/sail artisan migrate --seed
```
 6. ストレージリンクを作成
``` bash
./vendor/bin/sail artisan storage:link
```
## フロントエンド（Vue）
  1. Node コンテナに入る（Sail）
``` bash
./vendor/bin/sail npm install
```
 2. 開発サーバー起動
``` bash
./vendor/bin/sail npm run dev
```

## サンプルユーザーアカウント（動作確認用）
UsersTableSeederで登録されるメール認証済みのテストユーザーです。<br />
ユーザーによって見ることができるページが違います。

・ログインURL：http://localhost/

・会員ユーザー（一般会員）<br />
Email: member@example.com<br />
Password: password<br />
・医療機関スタッフユーザー<br />
Email: medical@example.com<br />
Password: password<br />
・理事ユーザー<br />
Email: director@test.com<br />
Password: password<br />
・管理者ユーザー<br />
Email: admin@example.com<br />
Password: password<br />
・医師会事務員ユーザー<br />
Email: staff@example.com<br />
Password: password<br />
・システム管理者ユーザー<br />
Email: system@example.com<br />
Password: password<br />

### システム管理者ユーザー（system）
``` text
システム管理者は、一般の管理者とは異なり、
システムの根幹に関わる設定のみを扱う特別ロールです。

・permission の CRUD
・お知らせカテゴリ（Notice-category）の CRUD
・コード変更が必要な設定項目の管理
・その他の管理機能にはアクセス不可（安全性のため）

RBAC の観点から、
「一般管理者が触れるべきでない領域」を system ロールに分離することで、
運用時の安全性と権限の明確化を実現しています。
```

## 実装機能一覧
### 会員向け機能
・ログイン/ログアウト<br />
・パスワードリセット<br />
・医療機関スタッフによる新規会員登録申請 / 管理者承認<br />
・お知らせ一覧<br />
・スケジュール一覧（カレンダー）<br />
・研修会一覧<br />
・書類一覧（ファイルダウンロード）<br />
・研修会動画一覧<br />
・問い合わせ報告一覧<br />

### メール機能
Resend（メール送信API）を使用して本番環境のメール送信を実装しています。<br />
現在実装済みのメール機能は以下の通りです。<br />
・会員登録時のメール認証（Verify Email）<br />
・メールアドレス変更時のメール認証（Verify Email）<br />
・会員登録が承認された時の登録完了メール（Welcome）<br />
※会員向けの通知メール機能は今後追加予定です。<br />

### 管理者向け機能
・会員管理（検索/編集）<br />
・医療機関管理（検索/編集）<br />
・コンテンツ投稿・編集<br />
・ロール管理（RBAC）<br />
・医療機関代表者設定<br />
・ロールターゲット機能（特定ロールのみ閲覧可能）<br />

## 使用技術
<img src="https://img.shields.io/badge/-PHP-777BB4.svg?logo=php&style=plastic"> <img src="https://img.shields.io/badge/-Laravel-E74430.svg?logo=laravel&style=plastic"> <img src="https://img.shields.io/badge/-Vue-42B883.svg?logo=vue.js&style=plastic"><img src="https://img.shields.io/badge/-MySQL-4479A1.svg?logo=mysql&style=plastic"> <img src="https://img.shields.io/badge/-Docker-1488C6.svg?logo=docker&style=plastic"> <img src="https://img.shields.io/badge/-TailwindCSS-06B6D4.svg?logo=tailwindcss&style=plastic"> <img src="https://img.shields.io/badge/-Railway-0F0F0F.svg?logo=railway&style=plastic"> <br />
  ・php 8.3<br />
  ・Laravel 10<br />
　・Vue 3<br />
  ・MySQL 8<br />
  ・Tailwind CSS<br />
  ・Docker<br />
  ・MailPit（ローカル環境でのメール送信確認）<br />
  ・Railway（デプロイ）<br />

## テスト
現在はテストコードを作成中です。<br />
今後以下の機能を対象に Feature / Unit テストを追加予定です。<br />

  1.ログイン/ログアウト<br />
  2.メール認証<br />
  3.パスワードリセット<br />
  4.会員情報取得<br />
  5.会員情報更新<br />
  6.お知らせ管理<br />
  7.文書管理<br />
  8.管理者による会員管理<br />
  9.RBAC（権限管理）<br />
  10.研修会管理<br />
  11.動画管理<br />
12.問い合わせ報告管理<br />
13.スケジュール管理<br />

### テストの実行方法
以下のコマンドでテストを実行してください。
``` bash
./vendor/bin/sail artisan test
```

## ER図
<img width="1191" height="1114" alt="Image" src="https://github.com/user-attachments/assets/239f30bf-60f0-4cf2-bf3c-f4083d059932" />

## URL
・会員画面：http://localhost/<br />
 ・phpMyAdmin：http://localhost:8080/<br />
・MailPit：http://localhost:8025<br />
・RailWay本番環境：https://member-portal-production-960b.up.railway.app
