# demo.prohit-okinawa.com 配置手順

サブドメインのドキュメントルートは `/prohit-okinawa.com/public_html/demo/` を使用します。

## ZIP内の配置先

- `demo/` の中身 → `/prohit-okinawa.com/public_html/demo/`
- `storage-demo/` → `/prohit-okinawa.com/storage-demo/`

`storage-demo` は必ず `public_html` の外に置きます。管理画面の文章、設定、セッションを保存する領域です。

## アップロード後

1. `https://demo.prohit-okinawa.com/` を確認
2. `https://demo.prohit-okinawa.com/admin.php` へログイン
3. サーバーパネルの「アクセス制限」で `/demo/` をONにする
4. 依頼主確認用のID・パスワードを追加する

`storage-demo`、`storage-demo/content`、`storage-demo/sessions`、`demo/uploads` はPHPから書き込み可能な権限にします。通常は755で動作し、保存に失敗する場合のみ775を使用します。
