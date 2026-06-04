# Contract Hub

小規模法人向けの契約・請求・入金管理システムです。

---

## Project Overview

Contract Hub は、Webサイト広告掲載・ITコンサルティング・AI導入支援などの業務委託契約を中心に、以下のライフサイクルを一元管理するための業務システムです。

> **注意：** このシステムは電子署名サービスそのものではありません。目的は契約・請求・入金の実務管理であり、PDF保存・同意ログ・SHA256ハッシュによる改ざん検知を通じて、合理的な証跡管理を実現します。

### 主な機能

| 分類 | 機能 |
|---|---|
| 顧客管理 | Company CRUD |
| 案件管理 | Project CRUD |
| 契約管理 | Contract CRUD |
| 契約証跡 | 契約PDF生成・SHA256保存・ハッシュ検証 |
| 電子同意 | 同意URL発行・外部同意フォーム・有効期限管理 |
| メール通知 | 同意依頼メール・締結完了通知メール |
| 請求管理 | Invoice CRUD・自動採番・10%税自動計算 |
| 入金管理 | Payment CRUD・残高管理・ステータス自動更新 |
| 請求書PDF | 請求書PDF生成・SHA256保存 |
| 請求書メール | 顧客への請求書PDF添付メール送信 |
| 監査ログ | 全主要操作のIP/UA付き記録 |

---

## Tech Stack

| 項目 | 内容 |
|---|---|
| Framework | Laravel 12 |
| PHP | 8.4 |
| Database | MySQL |
| Auth | Laravel Breeze |
| Template | Blade + Tailwind CSS |
| PDF | barryvdh/laravel-dompdf |
| Mail | Laravel Mail |

---

## Local Setup

```bash
git clone https://github.com/graystation/contract-hub.git
cd contract-hub

composer install
npm install

cp .env.example .env
php artisan key:generate
```

`.env` を編集して DB 接続情報を設定してください。

```bash
php artisan migrate --seed
npm run build
php artisan serve
```

ブラウザで `http://localhost:8000` を開き、シードで作成したユーザーでログインします。

---

## Test

```bash
php artisan test
```

現在 117 テストが PASSED の状態で維持されています。

新機能を追加する場合は、既存テストを壊さずに Feature test を追加してください。

---

## Mail

開発環境では `log` mailer を推奨します。

```dotenv
MAIL_MAILER=log
```

メール本文は以下で確認できます。

```bash
tail -f storage/logs/laravel.log
```

本番環境では SMTP を設定してください。詳細は [デプロイチェックリスト](docs/deployment-checklist.md) を参照。

---

## Storage

以下のディレクトリにPDFが保存されます。

| パス | 用途 |
|---|---|
| `storage/app/contracts/` | 契約書PDF |
| `storage/app/invoices/` | 請求書PDF |

**これらのディレクトリは定期バックアップの対象として設定してください。**

データベースの dump と合わせて、少なくとも週次でバックアップを取得することを推奨します。

---

## Japanese Font (PDF)

PDF生成には日本語フォントが必要です。

開発環境（macOS）では以下を使用しています。

```
/Library/Fonts/Arial Unicode.ttf
```

Linux サーバーへデプロイする場合は、IPAexGothic などの日本語フォントをインストールし、PDF テンプレート（`resources/views/contracts/pdf.blade.php`、`resources/views/invoices/pdf.blade.php`）の `@font-face` src パスを更新してください。

---

## Important Notes

- このシステムは電子署名サービス（PKI証明書、法的効力のある電子署名）ではありません
- 電子同意は同意ログの記録を目的としており、法的効力の保証はありません
- SHA256ハッシュは改ざん検知の補助手段です
- 本番環境では必ずHTTPSを使用してください
- 管理画面ルートはすべて認証必須（`auth` + `verified` middleware）です
- 外部公開ルートは同意フォーム（`/sign/contracts/{token}`）のみです

---

## License

Private — All rights reserved.
