
# フリマアプリ

## ✨ プロ入会テスト

#### 1\. 取引チャット機能の導入

  * **リアルタイムコミュニケーション**: 出品者と購入者間でのメッセージ送受信機能を実装。
  * **メッセージ管理**: `TransactionMessage`モデルを用いたメッセージの保存、取得、既読管理。
  * **画像添付**: チャットメッセージに画像を添付する機能を追加。
  * **編集・削除機能**: 投稿済みメッセージの編集および削除機能を追加。

#### 2\. ユーザー評価システムの構築
 
  * **取引完了後の評価**: 取引完了後に、購入者・出品者が互いを評価できる機能を追加。
  * **評価の記録**: `UserRating`モデルを用いて、評価点とコメントをデータベースに記録。
  * **平均評価の表示**: プロフィール画面でユーザーの平均評価を表示する機能。
  * **メール通知**: 商品購入者が取引を完了した際に、出品者へ通知メールを自動送信。 

## ⚠️ 未実装・仕様差異

以下の要件については時間が間に合わず、未実装または仕様が一部異なります。(2025/07/15)

  * **FN010メッセージ編集機能**: 未実装
  * **FN011メッセージ削除機能**: 未実装
  * **メッセージに画像を添付**: 修正間に合わず
  * **FN009	入力情報保持機能**:	未実装
  * **取引後の評価**: 想定される仕様と一部異なっています。
  * **出品ユーザーは取引完了をメールで確認することができる**: 未実装


-----

## 📋 再提出対応内容

### 🔧 主要な修正・改善点

#### 1\. Stripe決済機能の完全実装

  * **カード決済**: Stripe Checkoutの決済画面に正しく遷移するよう修正
  * **コンビニ決済**: PaymentIntentを使用したコンビニ決済を実装
  * **要件対応**: FN023「Stripeの決済画面に接続される」を完全実装
  * 決済完了後のデータベース登録と商品ステータス更新を実装

#### 2\. バリデーション・エラーメッセージの統一

  * 会員登録・ログイン時のエラーメッセージを要件通りに修正
  * 住所入力フォームのバリデーション形式を統一（郵便番号形式等）
  * メールアドレス重複チェックを追加
  * 商品出品時のバリデーションメッセージキーを修正

#### 3\. ユーザーフロー改善

  * 会員登録後のプロフィール設定画面遷移を実装（FN006対応）
  * ログアウト時のセッション無効化処理を強化
  * 購入完了後の適切な画面遷移とメッセージ表示

#### 4\. データベース設計の改善

  * `purchases`テーブルに`stripe_payment_id`カラムを追加
  * 商品状態の型統一（数値定数に統一）
  * Purchase.phpモデルのfillable属性とキャスト設定を修正

#### 5\. テストコードの改善

  * Stripe決済に対応したテストケースに修正
  * 実際のAPI通信を避けるモック処理を実装
  * 全79テストケースが正常に通過することを確認

-----

## 🗄️ データベース設計

### ER図

```mermaid
erDiagram
    users ||--o{ items : "出品"
    users ||--o{ purchases : "購入"
    users ||--o{ comments : "コメント"
    users ||--o{ favorites : "お気に入り"
    users ||--o{ transactions : "取引に関与"
    users ||--o{ transaction_messages : "メッセージ投稿"
    users ||--o{ user_ratings : "評価者/被評価者"
    
    items ||--o{ purchases : "購入される"
    items ||--o{ comments : "コメントされる"
    items ||--o{ favorites : "お気に入りされる"
    items ||--o{ category_item : "属する"
    items ||--o{ transactions : "取引対象"
    
    categories ||--o{ category_item : "含む"

    transactions ||--o{ transaction_messages : "含むメッセージ"
    transactions ||--o{ user_ratings : "評価対象"

    users {
        bigint id PK
        varchar name
        varchar email
        timestamp email_verified_at
        varchar password
        varchar avatar
        varchar postal_code
        varchar address
        varchar building_name
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }

    items {
        bigint id PK
        varchar name
        text description
        varchar brand
        integer price
        varchar image_url
        varchar condition
        bigint seller_id FK
        varchar status
        timestamp created_at
        timestamp updated_at
    }

    purchases {
        bigint id PK
        bigint user_id FK
        bigint item_id FK
        integer quantity
        integer price
        varchar payment_method
        varchar stripe_payment_id
        varchar shipping_postal_code
        text shipping_address
        varchar shipping_building_name
        varchar status
        timestamp purchased_at
        timestamp created_at
        timestamp updated_at
    }

    categories {
        bigint id PK
        varchar name
        timestamp created_at
        timestamp updated_at
    }

    category_item {
        bigint id PK
        bigint category_id FK
        bigint item_id FK
        timestamp created_at
        timestamp updated_at
    }

    favorites {
        bigint id PK
        bigint user_id FK
        bigint item_id FK
        timestamp created_at
        timestamp updated_at
    }

    comments {
        bigint id PK
        bigint user_id FK
        bigint item_id FK
        text content
        timestamp created_at
        timestamp updated_at
    }

    transactions {
        bigint id PK
        bigint item_id FK
        bigint seller_id FK
        bigint buyer_id FK
        varchar status
        timestamp created_at
        timestamp updated_at
    }

    transaction_messages {
        bigint id PK
        bigint transaction_id FK
        bigint user_id FK
        text message
        varchar image_path
        boolean is_read
        timestamp created_at
        timestamp updated_at
    }

    user_ratings {
        bigint id PK
        bigint transaction_id FK
        bigint rater_id FK
        bigint rated_user_id FK
        integer rating
        text comment
        timestamp created_at
        timestamp updated_at
    }
```

### テーブル一覧

#### users テーブル

| カラム名            | 型          | 説明               |
| :------------------ | :---------- | :----------------- |
| id                  | bigint      | ユーザーID（主キー） |
| name                | varchar(255)| ユーザー名         |
| email               | varchar(255)| メールアドレス（ユニーク） |
| email\_verified\_at   | timestamp   | メール認証日時     |
| password            | varchar(255)| パスワード（ハッシュ化） |
| avatar              | varchar(255)| プロフィール画像パス |
| postal\_code         | varchar(255)| 郵便番号           |
| address             | varchar(255)| 住所               |
| building\_name       | varchar(255)| 建物名             |
| remember\_token      | varchar(100)| ログイン保持トークン |
| created\_at          | timestamp   | 作成日時           |
| updated\_at          | timestamp   | 更新日時           |

#### items テーブル

| カラム名    | 型          | 説明               |
| :---------- | :---------- | :----------------- |
| id          | bigint      | 商品ID（主キー）   |
| name        | varchar(255)| 商品名             |
| description | text        | 商品説明           |
| brand       | varchar(255)| ブランド名         |
| price       | integer     | 価格               |
| image\_url   | varchar(255)| 商品画像URL        |
| condition   | varchar(255)| 商品状態           |
| seller\_id   | bigint      | 出品者ID（外部キー） |
| status      | varchar(255)| 商品状態（available/sold） |
| created\_at  | timestamp   | 作成日時           |
| updated\_at  | timestamp   | 更新日時           |

#### purchases テーブル

| カラム名             | 型          | 説明                   |
| :------------------- | :---------- | :--------------------- |
| id                   | bigint      | 購入ID（主キー）       |
| user\_id              | bigint      | 購入者ID（外部キー）   |
| item\_id              | bigint      | 商品ID（外部キー）     |
| quantity             | integer     | 購入数量               |
| price                | integer     | 購入価格               |
| payment\_method       | varchar(255)| 支払い方法             |
| stripe\_payment\_id    | varchar(255)| Stripe決済ID           |
| shipping\_postal\_code | varchar(255)| 配送先郵便番号         |
| shipping\_address     | text        | 配送先住所             |
| shipping\_building\_name| varchar(255)| 配送先建物名           |
| status               | varchar(255)| 購入ステータス         |
| purchased\_at         | timestamp   | 購入日時               |
| created\_at           | timestamp   | 作成日時               |
| updated\_at           | timestamp   | 更新日時               |

#### categories テーブル

| カラム名   | 型          | 説明               |
| :--------- | :---------- | :----------------- |
| id         | bigint      | カテゴリID（主キー） |
| name       | varchar(255)| カテゴリ名         |
| created\_at | timestamp   | 作成日時           |
| updated\_at | timestamp   | 更新日時           |

#### category\_item テーブル（中間テーブル）

| カラム名    | 型     | 説明               |
| :---------- | :----- | :----------------- |
| id          | bigint | ID（主キー）       |
| category\_id | bigint | カテゴリID（外部キー） |
| item\_id     | bigint | 商品ID（外部キー） |
| created\_at  | timestamp | 作成日時           |
| updated\_at  | timestamp | 更新日時           |

#### favorites テーブル

| カラム名   | 型     | 説明               |
| :--------- | :----- | :----------------- |
| id         | bigint | ID（主キー）       |
| user\_id    | bigint | ユーザーID（外部キー） |
| item\_id    | bigint | 商品ID（外部キー） |
| created\_at | timestamp | 作成日時           |
| updated\_at | timestamp | 更新日時           |

#### comments テーブル

| カラム名   | 型     | 説明               |
| :--------- | :----- | :----------------- |
| id         | bigint | コメントID（主キー） |
| user\_id    | bigint | ユーザーID（外部キー） |
| item\_id    | bigint | 商品ID（外部キー） |
| content    | text   | コメント内容       |
| created\_at | timestamp | 作成日時           |
| updated\_at | timestamp | 更新日時           |

#### transactions テーブル **(更新)**

| カラム名   | 型          | 説明               |
| :--------- | :---------- | :----------------- |
| id         | bigint      | 取引ID（主キー）   |
| item\_id    | bigint      | 商品ID（外部キー） |
| seller\_id  | bigint      | 出品者ID（外部キー） |
| buyer\_id   | bigint      | 購入者ID（外部キー） |
| status     | varchar(255)| 取引ステータス（例: pending, completed, canceled） |
| created\_at | timestamp   | 作成日時           |
| updated\_at | timestamp   | 更新日時           |

#### transaction\_messages テーブル **(新規)**

| カラム名       | 型          | 説明                   |
| :------------- | :---------- | :--------------------- |
| id             | bigint      | メッセージID（主キー） |
| transaction\_id | bigint      | 関連する取引ID（外部キー） |
| user\_id        | bigint      | メッセージ送信ユーザーID（外部キー） |
| message        | text        | メッセージ本文         |
| image\_path     | varchar(255)| 添付画像のパス（任意） |
| is\_read        | boolean     | メッセージの既読状態（true: 既読, false: 未読） |
| created\_at     | timestamp   | 作成日時               |
| updated\_at     | timestamp   | 更新日時               |

#### user\_ratings テーブル **(新規)**

| カラム名       | 型        | 説明               |
| :------------- | :-------- | :----------------- |
| id             | bigint    | 評価ID（主キー）   |
| transaction\_id | bigint    | 関連する取引ID（外部キー） |
| rater\_id       | bigint    | 評価を付けたユーザーID（外部キー） |
| rated\_user\_id  | bigint    | 評価されたユーザーID（外部キー） |
| rating         | integer   | 評価点（1〜5）     |
| comment        | text      | 評価コメント（任意） |
| created\_at     | timestamp | 作成日時           |
| updated\_at     | timestamp | 更新日時           |

-----

## 環境構築

Dockerビルド\</br\>

1.  `git clone リンク`
2.  `docker-compose up -d --build`\</br\>
    MySQLは、OSによって起動しない場合があるのでそれぞれのPCに合わせてdocker-compose.ymlファイルを編集してください。\</br\>

## Laravel開発環境

1.  `docker-compose exec php bash`
2.  `composer install`
3.  `.env.example`ファイルから`.env`を作成し、環境変数を変更
4.  `php artisan key:generate`
5.  `php artisan migrate`
6.  `php artisan db:seed`

### 🔑 Stripe設定（重要）

.envファイルに以下のStripe設定を追加してください：

```
# Stripe設定
STRIPE_PUBLIC_KEY="pk_test_xxxxxx"
STRIPE_SECRET_KEY="sk_test_xxxxxx"
```

### ✉️ メール通知設定（Mailhog/Mailtrap）

出品者へのメール通知機能を利用するため、MailhogまたはMailtrapの設定が必要です。
.envファイルに以下を追加または変更してください：

```
MAIL_MAILER=smtp
MAIL_HOST=mailhog # または mailtrap.io など
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🧪 テスト実行

```
# 全テスト実行
docker-compose exec php php artisan test

# 特定のテスト実行
docker-compose exec php php artisan test tests/Feature/PurchaseTest.php
```

## 🧑‍💻 テストユーザー

開発およびテストのために、以下のテストユーザーが利用可能です。

  * **出品者1**:
      * メールアドレス: `seller1@example.com`
      * パスワード: `password123`
      * 出品商品: CO01-CO05の商品
  * **出品者2**:
      * メールアドレス: `seller2@example.com`
      * パスワード: `password123`
      * 出品商品: CO06-CO10の商品
  * **購入者**:
      * メールアドレス: `buyer@example.com`
      * パスワード: `password123`
      * 備考: 商品は出品しません

-----


## 使用技術

  * PHP 8.4.4
  * Laravel 8.83.8
  * MySQL 8.02.6
  * **Stripe API** (決済処理)
  * **Mermaid** (ER図生成)

## 🌟 主要機能

  * ユーザー認証（会員登録・ログイン・ログアウト）
  * 商品一覧・詳細表示
  * 商品検索機能
  * 商品出品機能
  * お気に入り機能
  * コメント機能
  * **Stripe決済機能（カード・コンビニ）**
  * プロフィール管理
  * 購入履歴管理
  * **取引チャット機能**
      * 取引中の商品ごとのメッセージ送受信
      * メッセージへの画像添付
      * 未読メッセージ通知とソート
      * メッセージの編集・削除
  * **ユーザー評価機能**
      * 取引完了後の相互評価（1〜5段階とコメント）
      * プロフィールでの平均評価表示
      * 評価済みかどうかの判定

## URL

開発環境: http://localhost/\</br\>
phpMyAdmin: http://localhost:8080/

## 📝 備考

  * 本アプリケーションはテスト環境用です
  * Stripe決済はテストモードで動作します
  * 実際の決済は発生しません
  * 全79テストケースが正常に通過することを確認済み

-----
