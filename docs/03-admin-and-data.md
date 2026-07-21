# 管理画面・データ設計

## 1. 管理画面構成

```text
/admin/
├─ dashboard              公開状況、予約記事、最近の更新
├─ hero-slides            HEROスライド管理
├─ works                  施工事例管理
├─ news                   最新情報管理
├─ media                  画像管理
├─ inquiries              問い合わせ閲覧
├─ site-settings          基本情報・SNS・共通CTA
└─ users                  管理ユーザー（管理者のみ）
```

## 2. HEROスライド

| 項目 | 型・制約 |
|---|---|
| title | 必須、80文字程度 |
| lead | 任意、160文字程度 |
| image | 必須、PC/SP共通または個別指定 |
| image_alt | 装飾画像でなければ必須 |
| link_label / link_url | 任意 |
| overlay_color | HEXカラーピッカー |
| overlay_opacity | 0〜100%スライダー |
| dots_enabled | 真偽値 |
| dots_color | HEX |
| dots_opacity | 0〜100% |
| dots_size / dots_gap | 制限付き数値 |
| focal_x / focal_y | 画像の表示焦点、0〜100% |
| sort_order | ドラッグ操作で変更 |
| status | 下書き／公開 |

編集画面では実際のHERO比率によるPC・スマートフォンのライブプレビューを表示する。公開スライドが0件にならないよう警告し、初期状態ではグレー、アイボリー、ネイビーのプレースホルダーを登録する。

## 3. 施工事例

| 項目 | 型・制約 |
|---|---|
| title / slug | 必須、一意slug |
| summary / body | 概要、本文 |
| category | 新規開業、厨房改修、機器導入、内外装等 |
| services | 複数選択 |
| municipality | 市町村 |
| address_display | 公開用所在地 |
| latitude / longitude | 地図座標、範囲検証 |
| coordinate_precision | exact／approximate |
| map_visible | 地図掲載可否 |
| images | 最大10枚、並び順、alt、キャプション。先頭をメイン画像として使用 |
| region | 北部／中部／南部／宮古／八重山／久米島・その他離島 |
| completed_at | 完工日または年月 |
| status / published_at | 公開制御 |

座標入力は数値入力に加え、6エリアに分割した沖縄のオリジナルSVGマップ上の選択と、詳細位置の緯度・経度入力に対応する。非公開住所では概略座標を保存し、公開画面にも「位置は市町村周辺」と明示する。

## 4. 最新情報

| 項目 | 型・制約 |
|---|---|
| title / slug | 必須、一意slug |
| category | お知らせ、施工事例、製品情報等 |
| excerpt / body | 抜粋、本文 |
| featured_image | 任意、alt付き |
| status | 下書き／予約／公開／非公開 |
| published_at | 公開開始日時 |
| expires_at | 任意、公開終了日時 |
| seo_title / seo_description | 任意 |

## 5. 共通データ

- `media`: ファイル名、保存先、MIME、寸法、容量、alt、作成者
- `site_settings`: 会社情報、電話、営業時間、問い合わせ先、SNS、OGP画像
- `users`: 氏名、メール、パスワードハッシュ、役割、最終ログイン
- `inquiries`: 送信内容、対応状態、担当、内部メモ、受信日時

## 6. 権限と監査

- 管理者: 全機能、ユーザー管理、削除
- 編集者: HERO・施工事例・最新情報・メディアの編集と公開
- 閲覧者: 管理情報の閲覧のみ
- 更新者、更新日時を記録する
- 重要コンテンツは論理削除し、復元可能にする
- ログイン試行制限、セッション保護、パスワード再設定を用意する
