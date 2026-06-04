# Deployment Checklist

Contract Hub 本番環境移行前チェックリスト。

---

## 1. Environment

### .env 設定

- [ ] `APP_NAME=Contract Hub`
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` を本番 HTTPS URL に設定（例: `https://hub.example.com`）
- [ ] `APP_KEY` が設定されている（`php artisan key:generate` 実行済み）

### Database

- [ ] `DB_CONNECTION=mysql`
- [ ] `DB_HOST` / `DB_PORT` / `DB_DATABASE` を本番 DB に設定
- [ ] `DB_USERNAME` / `DB_PASSWORD` を本番 DB 認証情報に設定
- [ ] `DB_SOCKET` が必要なら設定（XAMPP / ローカルソケット接続）

### Mail

- [ ] `MAIL_MAILER=smtp`（本番では `log` から変更）
- [ ] `MAIL_HOST` / `MAIL_PORT` / `MAIL_USERNAME` / `MAIL_PASSWORD` を設定
- [ ] `MAIL_FROM_ADDRESS` を自社メールアドレスに設定
- [ ] `CONTRACT_ADMIN_EMAIL` を締結完了通知の受信アドレスに設定
- [ ] テストメール送信確認済み

---

## 2. Security

### 必須

- [ ] **HTTPS 必須** — Let's Encrypt または商用証明書を設定
- [ ] 管理者ユーザーのパスワードを強力なものに変更（デフォルト `Hub#2026!Kz@` から必ず変更）
- [ ] `/register` を無効化または IP 制限（小規模運用では自己登録を閉じる）
- [ ] `.env` ファイルを Web から直接参照できないことを確認
- [ ] `storage/` ディレクトリを直接公開しない（Nginx/Apache の設定確認）
- [ ] 契約PDF・請求書PDFが `public/` 以下に配置されていないことを確認

### 推奨

- [ ] Nginx / Apache でディレクトリリスティングを無効化
- [ ] `php artisan config:cache` でキャッシュ済み
- [ ] `php artisan route:cache` でキャッシュ済み
- [ ] `php artisan view:cache` でキャッシュ済み

---

## 3. Storage Backup

定期バックアップを設定してください。

### バックアップ対象

- [ ] `storage/app/contracts/` — 契約書PDF
- [ ] `storage/app/invoices/` — 請求書PDF
- [ ] MySQL データベース dump（`mysqldump contract_hub`）

### 推奨スケジュール

- 日次: データベース dump
- 週次: storage ディレクトリ全体
- 月次: オフサイトバックアップ（クラウドストレージ等）

---

## 4. Mail Testing

- [ ] 同意依頼メール送信テスト（`ContractSignRequestMail`）
- [ ] 締結完了通知メール送信テスト（`ContractSignedNotificationMail`）
- [ ] 請求書メール送信テスト（`InvoiceSendMail`）
- [ ] 添付PDFが正常に受信できることを確認

---

## 5. PDF Generation

- [ ] 日本語フォントがサーバーにインストール済みであることを確認
- [ ] PDF テンプレート（`contracts/pdf.blade.php`、`invoices/pdf.blade.php`）の `@font-face` src パスをサーバーのフォントパスに更新
- [ ] 手動で PDF 生成・ダウンロード確認済み

---

## 6. Application Check

```bash
# テスト全件通過確認
php artisan test

# フロントエンドビルド
npm run build

# キャッシュクリア（設定変更後）
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 本番キャッシュ（デプロイ後）
php artisan config:cache
php artisan route:cache
php artisan view:cache

# マイグレーション確認
php artisan migrate --force
```

---

## 7. Register Route

デフォルトでは `/register` が有効です。

小規模運用でユーザー自己登録が不要な場合は、`routes/auth.php` の register ルートをコメントアウトするか、管理者のみアクセスできるよう IP 制限を設けてください。

```php
// routes/auth.php — 自己登録を閉じる場合はコメントアウト
// Route::get('register', [RegisteredUserController::class, 'create'])...
// Route::post('register', [RegisteredUserController::class, 'store'])...
```

---

## 8. Post-Deployment Smoke Test

- [ ] ログインできる
- [ ] 顧客・案件・契約の CRUD が動作する
- [ ] 契約 PDF を生成・ダウンロードできる
- [ ] 同意 URL を発行・外部同意フォームを表示できる
- [ ] 請求書 CRUD が動作する
- [ ] 請求書 PDF を生成・ダウンロードできる
- [ ] 請求書メールを送信できる
- [ ] ダッシュボードが正常表示される
- [ ] AuditLog が記録されている
