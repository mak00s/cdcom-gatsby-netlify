# Concept Diagram 公式サイト情報

最終確認日: 2026-08-11

このファイルを、Concept Diagram 公式サイトに関する情報の基準点として使用する。

## 現在の構成

### WordPressサイト

- 公開URL: https://concept-diagram.com/
- ホスティング: さくらインターネット
- 用途: コンセプトダイアグラムの公式解説、描き方、FAQ、活用例、お問い合わせ
- CMS: WordPress
- サーバー応答: nginx / PHP 8.3.32
- SSHユーザー: `mak-s`
- SSH認証: 公開鍵認証
- ローカルの関連リポジトリ: `/Users/mak/Repos/github_mak00s/concept-diagram.com`

`concept-diagram.com` リポジトリはWordPressから同期されたMarkdown原稿の保管場所であり、WordPress本体、テーマ、プラグイン、サーバー設定のソースコード一式ではない。

### 2026-08-05作業後の運用状態

- トップページは「最近の投稿」を外し、「コンセプトダイアグラムとは」→「描き方」→「FAQ」→「活用例」の学習導線へ変更済み
- 有料セミナー販売、会員サービス、利用者フォーラムは終了。関連ページと商品は下書き化済み
- WooCommerce注文58件と返金1件、Simple Membership会員108件を運用DBから削除済み
- 販売・決済・会員・フォーラム・デバッグ系プラグイン14件を本番から削除済み
- WooCommerce等が残したAction Schedulerデータと関連Cronイベントを整理済み
- 削除前DBバックアップ: `/home/mak-s/backups/concept-diagram-20260805-preprod/database-pre-data-purge.sql`（権限`600`）
- Contact Form 7は継続。宛先は`mak00s@gmail.com`
- reCAPTCHAが全送信をスコア`0.00`で拒否したため設定を退避して無効化し、公開画面から送信成功を確認済み
- Search Consoleのドメインプロパティ`concept-diagram.com`はTXTレコードによる所有権確認と追加を完了済み
- Search Consoleでサービスアカウントをフル権限ユーザーとして追加済み。Google Cloud側のSearch Console API有効化はパスキー本人確認待ち

### Gatsby / Netlifyサイト

- 関連リポジトリ: `/Users/mak/Repos/github_mak00s/cdcom-gatsby-netlify`
- GitHub: https://github.com/mak00s/cdcom-gatsby-netlify
- 用途: Concept Diagram公式サイトのGatsby版として作成されたプロジェクト
- 技術構成: Gatsby 2、React 16、Netlify CMS
- 当時の実行環境: Node.js 12、Yarn 1.22.4
- 最終更新時期: 2021年3月

リポジトリ内にはConcept Diagram向けの記事やページがあるが、依存関係と実行環境が古く、現在のNode.js環境でそのまま運用できるとは限らない。

## 障害と復旧記録

2026-08-05に、WordPressサイトのトップと多数の固定ページがHTTP 404になる障害を確認し、同日復旧した。

| URLまたは機能 | 復旧前 | 復旧後 |
| --- | --- | --- |
| `/` | HTTP 404 | HTTP 200 |
| `/about/` | HTTP 404 | HTTP 200 |
| `/contact/` | HTTP 404 | HTTP 200 |
| `/privacy-policy/` | HTTP 404 | HTTP 200 |
| `/cart/` | HTTP 404 | HTTP 200 |
| `/account/` | HTTP 404 | HTTP 200 |
| `/note/` | HTTP 200 | HTTP 200 |
| `/seminar/` | HTTP 200 | HTTP 200 |
| `/seminar/enquete/` | HTTP 404 | HTTP 200 |
| `/forums/` | HTTP 200 | HTTP 200 |
| `/wp-login.php` | HTTP 200 | HTTP 200 |
| `/wp-json/` | HTTP 200 | HTTP 200 |

### 原因

bbPressのオプション `_bbp_private_forums` に、すでに存在しない非公開フォーラムID `1488` が残っていた。bbPress 2.6.10がこのIDを除外するためのメタクエリを通常ページにも適用し、フォーラム用メタデータを持たない固定ページを検索結果から除外していた。

### 実施した変更

- 変更前にデータベース全体をバックアップ
- `_bbp_private_forums` から、存在しないID `1488` を除去
- bbPress自体は有効なまま維持
- 主要ページ、フォーラム、WordPressログイン、REST APIを外部から検証

バックアップ:

`/home/mak-s/db/concept-diagram-pre-repair-20260805.sql`

バックアップのファイル権限は `600`。

### SSH接続状況

- `mak-s@concept-diagram.com` でSSHサーバーへの到達を確認済み
- サーバーのED25519ホスト鍵はローカルの `known_hosts` に登録済み
- SSH接続名: `concept-diagram-sakura`
- 接続先サーバー: `www3780.sakura.ne.jp`
- サーバーOS: FreeBSD 11.2-RELEASE-p17
- 専用秘密鍵: `/Users/mak/.ssh/id_ed25519_sakura_concept_diagram`
- 専用公開鍵: `/Users/mak/.ssh/id_ed25519_sakura_concept_diagram.pub`
- 公開鍵フィンガープリント: `SHA256:MXAIh3qcehUrVKIs8P/aaIa8RhyVxBEyj6Kki5EaQCY`
- 公開鍵はさくら側の `mak-s` ユーザーへ登録済み
- `ssh concept-diagram-sakura` による鍵認証を確認済み

## WordPress保守監査（2026-08-05）

本番環境を変更せず、SSHとWP-CLIで状態を確認した。障害は復旧済みだが、更新幅が大きいため、本番での一括更新は行わない。

### 基盤と主要コンポーネント

| 項目 | 現在 | 更新候補・所見 |
| --- | --- | --- |
| WordPress | 7.0.2 | 2026-08-05に本番更新・DBスキーマ確認済み |
| PHP | 8.3.32 | 2026-08-05に本番切替・主要ページ確認済み |
| MySQL | 8.0.44 | 2026-08-06にさくらのDBアップグレード機能で移行し、全テーブル検査済み |
| テーマ Snow Monkey | 25.4.7 | 2026-08-05に本番更新、主要ページ確認済み |
| Contact Form 7 | 6.1.6 | 公開画面から送信成功を確認済み |
| SiteGuard | 1.8.7 | ログインURLと管理者アクセスを確認済み |

WordPress、PHP、MySQL、主要テーマ・プラグインの大幅更新は完了した。今後は一括更新を避け、Snapupステージング、直前バックアップ、主要ページと問い合わせの回帰試験をセットで行う。

### 監査結果

- データベース全テーブル: `wp db check` で正常
- WP-Cron: 定期イベントの実行予定あり
- サイト一式の使用量: 約768MB
- アップロード: 約112MB
- データベース使用量: 約16MB
- 2026-08-05に取得したバックアップはDBのみ
- サーバー上の完全バックアップは2019年・2020年のものしか確認できず、現状復元用としては古い
- WordPress管理者は4アカウント。各アカウントが現在も必要か、所有者確認が必要
- 本番で `Debug Bar` と `Show Current Template` が有効。不要なら検証後に停止・削除する
- `DISALLOW_FILE_EDIT` は未設定。更新後、管理画面からのテーマ・プラグイン編集を禁止することを検討する
- コアチェックサムは `wp-config-sample.php` の差分1件。実設定ファイル `wp-config.php` の異常を示すものではないが、更新時に正規ファイルへ戻す
- プラグイン検証では、バックアップ領域・ログの生成ファイルと、公式チェックサムがない独自プラグインが検出された。独自プラグイン `kosgis-add-function`、`swpm-bbpress` などはPHP 8対応を個別確認する
- SSH接続時に、サーバーが耐量子鍵交換へ未対応との警告あり。共有サーバー基盤側の事項として、さくらの更新・移行候補を確認する

### 推奨する更新順序

1. さくらのバックアップ＆ステージング（Snapup）で、DBとWordPress全ファイルを含むスナップショットを取得する。加えて、定期的にローカルまたは別ストレージにも複製する。
2. 取得したスナップショットからSnapupのステージング環境を作る。メール送信、決済、検索エンジンへの公開、外部Webhookは停止またはテスト設定にする。
3. ステージングで自動更新を管理下に置き、WordPress、テーマ、プラグインを小さな単位で更新する。各段階でDBバックアップを取る。
4. トップ、固定ページ、投稿、画像、検索、問い合わせ、会員登録・ログイン、bbPress、カート、注文メール、Stripe、PayPalを回帰試験する。
5. Snapupで選択可能なPHP 8.1で互換性を確認し、本番では更新後にPHP 8.3へ切り替えて再確認する。
6. MySQL 5.7から8.0へのアップグレードを予約・実行し、完了後に`wp-config.php`の`DB_HOST`（必要なら`DB_PASSWORD`も）をMySQL 8.0用へ変更する。切り戻し用に旧接続情報を保持する。
7. 本番反映前にメンテナンス時間とロールバック手順を決め、直前バックアップを取得して反映する。
8. 反映後に不要なテーマ・プラグイン・管理者を整理し、日次バックアップ、死活監視、更新確認を定期運用する。

参考:

- WordPress要件: https://wordpress.org/about/requirements/
- PHPサポート状況: https://www.php.net/supported-versions.php
- さくらのPHPバージョン変更手順: https://help.sakura.ad.jp/rs/2241/
- さくらのバックアップ＆ステージング: https://help.sakura.ad.jp/rs/2197/

### Snapup利用方針

- 対象プラン: スタンダード、プレミアム、ビジネス、ビジネスプロ、マネージド。ライトは対象外
- 料金: 無料
- スナップショット: 最大8個
- レンタルサーバの上限: 1スナップショット60GB、DB 1GB
- ステージング: 同時に1環境、作成から90日で自動削除
- 現在のサイト約768MB、DB約16MBは容量上限内
- WordPress用サイトとして `/home/mak-s/www/concept-diagram.com/cd` を登録し、本番からスナップショットを取得する
- Snapupだけに依存せず、障害・解約・誤削除に備えて外部バックアップも保持する
- ステージングは本番と実行環境が異なるため、最終的なPHP切替と決済・メールの確認は本番反映手順にも含める
- SnapupではPHP 8.1／MySQL 5.7までの検証となるため、本番のPHP 8.3／MySQL 8.0切替後にも短時間の回帰試験を行う

### 本番のPHP 8.3・MySQL 8.0移行方針

- WordPress公式推奨環境はPHP 8.3以上、MySQL 8.0以上
- 先にWordPress本体・テーマ・継続プラグインを検証済み構成へ更新し、PHP 7.4／MySQL 5.7上で正常性を確認する
- 次にPHP 8.3へ切り替え、主要ページ、管理画面、問い合わせ画面、Cron、エラーログを確認する。問題時はPHP 7.4へ戻す
- PHP確認後にさくらのデータベースアップグレード機能でMySQL 8.0へコピーする。MySQL 5.7からの場合、通常はDB名の変更不要
- アップグレード完了メール後、`wp-config.php`の`DB_HOST`を`mysql80.アカウント名.sakura.ne.jp`形式へ変更する。パスワードが異なる場合は`DB_PASSWORD`も変更する
- 切替直前には更新停止時間を設ける。切替後に投稿・設定を変更すると旧DBへ戻した際に差分が失われるため、検証完了まで更新を行わない
- 旧MySQL 5.7の接続情報と直前SQLダンプを保持し、問題時は`wp-config.php`を旧DB接続へ戻せるようにする

### Snapup実施状況（2026-08-05）

- 本番サーバーからWordPressスナップショットを作成済み
- 作成したスナップショットからステージング環境を作成済み
- 現在のステージングURL: https://rough-math-45356355.stg-s.snapup.jp/
- 旧ステージングURL: https://dry-art-59555852.stg-s.snapup.jp/（PHP 7.4検証時）、https://tiny-kiwi-60239092.stg-s.snapup.jp/（初回作成時）
- 外部アクセスはHTTP Digest認証で保護され、未認証時にHTTP 401となることを確認済み
- Digest認証後のWordPress表示、PHP 8.1.20、MySQL 5.7.20を確認済み
- ステージングから本番への一括リリースは、検証期間中に本番で増えた注文・会員・投稿を上書きする可能性があるため、現時点では行わない
- 2026-08-05の管理画面確認では、ステージングはWordPress 7.0.2の初期状態で、本番のテーマ・プラグイン・サイト名が反映されていなかった
- Snapup上で「作成済みスナップショット」→「ステージングサーバーへセット」を実行し、「サイトURLをリリース先にあわせる（既定）」を選ぶ必要がある
- その後スナップショットをセットし、本番サイト名、Snow Monkey、主要ページ、プラグインの複製を確認済み
- 更新済みステージングのスナップショットから環境を再作成し、PHPを8.1.20へ変更済み。本番はPHP 7.4.33
- ステージングは検索エンジン非公開（`blog_public=0`）に設定済み

## 販売・会員機能の終了記録（2026-08-05）

有料セミナー販売と会員向けコンテンツは今後利用しない。ステージングで依存関係と表示を確認後、関連機能を本番から段階的に撤去する。

### 撤去前に確認したデータ

- WooCommerce商品: 10件（公開8、非公開1、下書き1）
- WooCommerce注文: 58件（2018-10-12〜2022-05-20）
- 本番のStripe決済ゲートウェイは有効。PayPalほかは無効
- 公開商品8件のうち、商品ID `1864`（10,000円）が `instock`。ほか7件は `outofstock`
- Simple Membership会員: 108件（すべてactive、2018-10-01〜2022-05-20）
- bbPressフォーラム: 5件、トピック: 9件、返信: 0件。最終投稿は2019-06-23
- 関連固定ページ: 会員登録、ログイン、パスワード再設定、カート、支払い、マイアカウント、フォーラム

注文・会員データには個人情報が含まれるため、削除前のDBダンプを権限`600`で退避した。運用DBからは2026-08-05に削除済みで、バックアップは障害復旧以外に利用せず、移行安定後に保存期間を決めて廃棄する。

販売終了後も本番のStripeと在庫あり商品が残っているため、誤購入防止として本番の販売受付停止を優先する。これはプラグイン削除とは分け、取り消し可能な設定変更として実施する。

### 削除済みプラグイン

販売終了に伴う候補:

- `woocommerce`
- `woocommerce-gateway-stripe`
- `woocommerce-paypal-payments`
- `mailchimp-for-woocommerce`
- 非アクティブのAmazon Payments、旧PayPal Express Checkout

会員・フォーラム終了に伴う候補:

- `simple-membership`
- `swpm-bbpress`
- `bbpress`
- `bbpress-permalinks-with-id`
- 非アクティブのSnow Monkey bbPress Support

上記に加え、`woocommerce-gateway-amazon-payments-advanced`、`woocommerce-gateway-paypal-express-checkout`、`snow-monkey-bbpress-support0`、`debug-bar`、`show-current-template`、`jetpack-markdown`を削除した。WooCommerceの注文・返金、Simple Membershipの会員テーブル、Action Schedulerのジョブとログ、関連Cronイベントも削除済み。

### ステージングで実施済み

- 停止: WooCommerce、Stripe、PayPal Payments、Mailchimp for WooCommerce
- 停止: Simple Membership、SWPM bbPress連携、bbPress、bbPress Permalinks with ID
- 停止: Debug Bar、Show Current Template
- 下書き化: 会員登録、会員ログイン、パスワード再設定、カート、決済、マイアカウント、フォーラム
- 下書き化: WooCommerce商品10件と関連メニュー項目
- 保持: 注文58件、会員108件、フォーラム・トピックおよび関連DBテーブル
- 継続ページの確認: トップ、About、問い合わせ、プライバシーポリシー、note、セミナー、アンケートはHTTP 200
- 終了ページの確認: カート、決済、アカウント、会員登録、パスワード再設定、フォーラムはHTTP 404
- `/login/` はSiteGuardのWordPressログイン経路としてHTTP 200を維持

### ステージング更新結果

- WordPress: 5.8.13 → 7.0.2、DBスキーマ更新完了
- PHP: 8.1.20
- MySQL: 5.7.20
- MySQL: 5.7
- Snow Monkey: 14.2.2 → 25.4.7
- Contact Form 7: 5.5.6.1 → 6.1.6、再有効化して問い合わせページの読み込みを確認
- SiteGuard: 1.7.12 → 1.8.7、有効。更新後にログインURLと画像認証が再生成された
- Duplicate Post: 4.5 → 4.7、管理補助プラグインのため無効のまま
- Public Post Preview: 2.10.0 → 3.1.2、有効のまま更新完了
- 継続する主要ページはHTTP 200、致命的エラー表示なし
- 販売・会員・フォーラム関連ページは下書き、関連プラグインは無効の状態を維持
- SiteGuardのカスタムログインURLはHTTP 200、通常の`/wp-login.php`はHTTP 404で保護されていることを再確認
- Contact Form 7はreCAPTCHA設定の不整合を解消後、公開フォームから送信成功を確認済み

### 本番反映状況（2026-08-05）

- 直前DBダンプ: `/home/mak-s/backups/concept-diagram-20260805-preprod/database.sql`（権限600）
- 直前ファイルアーカイブ: `/home/mak-s/backups/concept-diagram-20260805-preprod/wordpress-files.tar.gz`（権限600、gzip検査・一覧読取済み）
- MySQL 5.7接続設定の退避: `/home/mak-s/backups/concept-diagram-20260805-preprod/wp-config.mysql57.php`（権限600）
- コンテンツ更新直前DBダンプ: `/home/mak-s/backups/concept-diagram-20260805-preprod/database-pre-content-rewrite.sql`（権限600）
- コンテンツ更新スクリプトの記録: `/home/mak-s/backups/concept-diagram-20260805-preprod/content-update-20260805.php`（権限600）
- MySQL 8切替直前の設定退避: `/home/mak-s/backups/concept-diagram-20260805-preprod/wp-config.php.pre-mysql8-switch`
- MySQL 8切替・コンテンツ修正前DBダンプ: `/home/mak-s/backups/concept-diagram-20260805-preprod/database-mysql8-pre-content-fixes.sql`（5.9MB、権限600）
- WordPress: 5.8.13 → 7.0.2、DBスキーマ確認済み
- Snow Monkey: 14.2.2 → 25.4.7
- Contact Form 7: 5.5.6.1 → 6.1.6
- Contact Form 7「お問い合わせ」（ID 40）の管理者向け送信先: `mak00s@gmail.com`。自動返信は送信者の`[email]`宛て
- Contact Form 7「セミナー参加者アンケート」（ID 1775）の管理者向け送信先: `seminar@concept-diagram.com`。自動返信は送信者の`[your-email]`宛て
- セミナー参加者アンケートへのメニューリンクは存在しない。公開ページ2件（ID 1773、1908）とアンケート用Contact Form 7（ID 1775）を下書き化し、旧URLのHTTP 404を確認
- 問い合わせフォームの自動送信テストは、直接APIと通常ブラウザ画面の両方でContact Form 7に`spam`判定され、メール処理へ到達しなかった。reCAPTCHAを迂回せず、人のブラウザから追加確認する
- SiteGuard: 1.7.12 → 1.8.7
- Duplicate Post: 4.5 → 4.7、更新後は無効
- WooCommerce、Stripe、PayPal Payments、Mailchimp for WooCommerce、Simple Membership、SWPM bbPress、bbPress、bbPress Permalinks with IDを無効化
- Debug Bar、Show Current Templateを無効化
- JP Markdown（`jetpack-markdown`）は互換性エラーを起こしたため無効化。停止後に公開サイトのHTTP 200を確認
- 商品10件と販売・会員・フォーラム関連ページを下書き化。関連URLはHTTP 404
- トップ、About、問い合わせ、プライバシーポリシー、note、セミナー、REST APIはHTTP 200で、致命的エラー表示なし
- PHP: 7.4.33 → 8.3.32。主要ページ、REST API、WP-CLI、Cron一覧で致命的エラーなし
- `/home/mak-s/www/php.ini`からPHP 5.2用`extension_dir`と旧`apc.so`読込を除去。OPcacheが有効であることを確認
- 旧php.iniの退避先: `/home/mak-s/www/php.ini.pre-php83-20260805`
- Public Post Preview: 2.10.0 → 3.1.2、有効のまま更新完了
- MySQL 8.0アップグレードは2026-08-06 00:00（JST）に実施。移行前後で`posts`、`postmeta`、`options`、`users`、`usermeta`の件数一致を確認
- `wp-config.php`の`DB_HOST`を`mysql705.db.sakura.ne.jp`から`mysql80.mak-s.sakura.ne.jp`へ変更
- MySQL 8.0.44でWordPress 7.0.2、全DBテーブル、トップ、描き方、問い合わせ、管理画面ログイン転送を確認済み
- 旧MySQL 5.7の接続情報と切替前バックアップは、移行安定確認後まで切り戻し用として保持する
- さくらのコントロールパネルでも、2026-08-06 00:00予約分が「アップグレードが完了しました」と表示されることを確認
- この予約では旧MySQL 5.7上の6データベースがMySQL 8.0へコピーされた。Concept Diagram以外では、`oshiete-cho.com`のWordPress、`store.concept-diagram.com`、`wiki.evar7.org`のMediaWiki設定が引き続き旧MySQL 5.7ホストを参照している
- 上記3サイトの継続・廃止判断と、継続サイトのMySQL 8.0接続切替が完了するまで、旧MySQL 5.7および旧データベースを削除しない

## 公開コンテンツ監査（2026-08-05）

### 編集上の選定ルール

- 執筆者だけで公開・非公開を決めず、内容の正確性、実用性、現在性、読者が誤解する可能性で判断する
- 執筆者表示は情報の来歴として維持する。内容が有用なら寄稿記事も公開を継続する
- 記事間で定義や手順が矛盾する場合は、後発の公式情報と現在の手法を基準に本文を修正する
- 終了済みイベント、存在しない機能への誘導、実態と異なる説明は、執筆者に関係なく非公開または更新対象とする

### 非公開推奨

| ID | 種別 | コンテンツ | 理由・付随対応 |
| --- | --- | --- | --- |
| 2122 | 投稿 | `GA4・GTMサーバーを導入しながら…講座` | 2022年5月開催・59,800円の終了済み有料講座。申込先も404。トップとフッターの「最近の投稿」に表示中なので下書き化する |
| 1408 | 固定ページ | `申込可能セミナー` | 本文は「今後開催予定」の1文だけで、開催予定なし。下書き化し、メニュー項目ID 1409・1572も非公開化する |
| 1429, 1431, 1491, 1510, 1925 | forum | 旧フォーラム5件 | 会員・フォーラム運用終了済み。現在はbbPress停止により404だが、DB上は公開のため下書き化する |
| 1513, 1930, 1948, 1951, 1952, 1960, 1961 | topic | 旧トピック7件 | セミナーフォロー、自己紹介、旧コミュニティ投稿。現在は404。下書き化して公開データ上も終了状態にする |
| 1773, 1908 | 固定ページ | セミナー参加者アンケート2件 | サイト内メニューからのリンクなし。不要との判断により下書き化済み、旧URLは404 |
| 1775 | Contact Form 7 | セミナー参加者アンケート | 不要との判断により下書き化済み |
| 1672 | 投稿 | 「ひとりブレスト」 | 個人的な体験・語り口が中心で、旧Facebook投稿や講座・アンケートにも依存。内容ベースで優先度低の参考アーカイブ候補 |
| 1785 | 投稿 | 「顧客視点の解析」 | 一般的な解析解説で、旧UA用語が中心。現在のGA4環境では誤解を招くため、更新まで非公開候補 |

### 優先更新

| ID | コンテンツ | 更新理由 |
| --- | --- | --- |
| 1382 | ホーム | 「提唱から早10年」が古い。最近の投稿の先頭が終了済みGTM講座。サイトの現在の目的と更新方針へ書き換える |
| 140 | このサイトについて | 会員フォロー、メール配信、有料セミナーの予定・案内が残る。現在の非営利な情報アーカイブ／公式解説サイトとして再定義する |
| 141 | お問い合わせ | 「セミナーや企業向け研修」の相談文言を、現在受け付ける問い合わせ範囲へ変更する。フォーム送信は自動テストがreCAPTCHAにspam判定されたため手動確認が必要 |
| 360 | プライバシーポリシー | 現状と合わない会員登録、商品購入、銀行口座、カード番号、運転免許証、決済情報等の記載が多い。実際の問い合わせ、GA/GTM、Cookie、サーバーログに合わせて全面改訂する |
| 1500 | コンセプトダイアグラムとは | 中核記事として内容は有用。終了済みフォーラム・セミナーへの言及、古い短縮Amazonリンク、表現・誤記を更新する |
| 1389, 1391 | 描き方（前編・後編） | 手順・具体例・図があり、入門資料として有用なので公開継続。旧フォーラム・公式ワークショップ・プラクティショナー制度への誘導を除去する。後編の「理想は8ステップ」は後発FAQ（ID 1709）の「5〜6、最大6」と矛盾するため修正する。未公開の「次回KPI設定」予告も整理する |
| 1597, 1600, 1602, 1605, 1681 | FAQ 5件 | BtoBでの対象者設定、施策と心理変容の分離、サービス名を入れない理由、リーダー参加時の心理的安全性、右下方向の意味を具体的に説明しており実用的。公開継続し、現在の用語・手法との軽微な整合確認を行う |

### 継続公開候補

- 「コンセプトダイアグラムとは」（ID 1500）、「ステップ数はいくつが適切？」（ID 1709）、「なぜ軸が2つなのか？」（ID 1701）
- FAQ 5件（ID 1597、1600、1602、1605、1681）は短いが、各記事が1つの具体的な疑問へ実用的に回答しているため公開継続
- 描き方前後編（ID 1389、1391）は、矛盾と終了済み導線を修正したうえで、サイト内に手順解説が存在する価値を優先して公開継続
- ブログ一覧（ID 121）は投稿一覧ページ、ホーム（ID 1382）はテーマ側の動的構成のため本文文字数0でも低品質とは判定しない

### 編集停止時刻

- MySQL 8.0アップグレードは2026-08-06 00:00 JST実行予定。開始時刻の揺れを考慮し、2026-08-05 23:45以降は管理画面でのコンテンツ・設定変更を停止する

### コンテンツ更新実施結果（2026-08-05）

- 「顧客視点の解析」（ID 1785）を下書き化、旧URLはHTTP 404
- GTMサーバー講座（ID 2122）と「申込可能セミナー」（ID 1408）を下書き化。関連メニュー項目ID 1409・1572も下書き化
- 旧フォーラム5件、旧トピック7件を下書き化。`/forums/`はHTTP 404
- 描き方前編・後編（ID 1389、1391）は公開継続。「講座でも使っている」、旧フォーラム・ワークショップ制度への誘導、未公開の次回予告を除去
- 描き方後編のステップ数を、後発FAQに合わせて「5〜6つ程度が目安」へ修正
- 「コンセプトダイアグラムとは」（ID 1500）から旧セミナー参加者・フォーラムへの誘導を除去
- ホームの説明を現在の定義・運営方針へ更新。終了済みGTM講座と「申込可能セミナー」のリンクが消えたことを確認
- 「このサイトについて」を公式情報サイトとしての現在の目的へ全面更新
- 「お問い合わせ」からセミナー・研修募集を除去し、終了サービスを明記。Contact Form 7は維持
- プライバシーポリシーを、問い合わせ情報、アクセス情報、Google Analytics／Google Tag Manager、注文・会員データの運用DBからの削除と復旧バックアップの保持実態に合わせて全面更新
- 上記の継続ページはすべてHTTP 200、致命的エラー表示なし

### ナビゲーション・表示不具合修正（2026-08-06）

- Topから、終了サービスについての案内文「現在、有料セミナーの販売、会員向けコンテンツ、利用者フォーラムの運営は行っていません。」を削除。Aboutと問い合わせの運営情報は維持
- グローバルナビを「コンセプトダイアグラムとは」「描き方」「FAQ」「お問い合わせ」の順に整理し、「描き方」は前編（ID 1389）へリンク
- 描き方前後編の説明画像9点と、ひとりブレスト記事の本文画像1点に内容を表す代替テキストを追加
- 描き方前編の引用ブロックに残っていた余分な`</p>`を除去
- ひとりブレスト記事の壊れたFacebook埋め込みを、ログインが必要なことを明記した通常リンクへ変更
- ボタンが表示されない旧Facebook Like誘導枠を撤去し、通常のSNS共有ボタンは維持
- 「コンセプトダイアグラムとは」の404になったAmazon短縮URLを、KADOKAWA公式書籍ページへ変更
- 小杉聖の著者欄から、セミナーや勉強会を現在も定期開催中と読める古い記述を削除
- Snow Monkeyから挿入されていた無効な`GTM-59MP4P6`を削除。公開HTMLは有効な`GTM-5TDB7H2`の1系統のみ
- XMLサイトマップ掲載21ページと、リンク・画像・iframe 94件を再検査。公開ページ障害と実リンク切れは0件。LinkedInは自動検査のみHTTP 999だが、通常ブラウザで本人プロフィール表示を確認

## 今後のサイト改善ロードマップ

### 短期（0〜2週間）: 安定化と迷わない導線

1. [完了] MySQL 8.0へ移行し、DBホスト切替、全テーブル、主要ページ、管理画面を確認。旧MySQL 5.7と直前バックアップは安定確認まで保持する
2. 問い合わせフォームを人のブラウザから送信し、管理者メールと自動返信の到達を確認する。失敗時はreCAPTCHA、送信元ドメイン、SPF/DKIM/DMARC、メールログを順に確認する
3. [完了] トップの「最近の投稿」中心の構成をやめ、「コンセプトダイアグラムとは」→「描き方」→「FAQ」→「活用例」の固定された学習導線を主役にする
4. 終了ページは代替コンテンツがなければ404または410を維持し、代替ページが明確な場合だけ301リダイレクトを設定する。無関係なホームへの一括リダイレクトは行わない
5. Search Consoleのドメインプロパティ追加は完了。XMLサイトマップを送信し、終了済みセミナー・会員・フォーラムURLのインデックス状況と、新しい主要ページのクロール状況を確認する
6. [完了] 復旧用アーカイブ確認後、WooCommerce、決済、会員、bbPress、デバッグ系プラグイン14件を削除し、注文・会員データを運用DBから削除する
7. [完了] WooCommerce等が残したCronイベントとAction Schedulerジョブを整理する
8. Snapupの日次バックアップ、外部バックアップ、死活監視、WordPress更新通知を運用化する

### 中期（1〜3か月）: 公式情報としての品質向上

1. 主要導線を「概要」「描き方」「FAQ」「事例」「お問い合わせ」の5領域へ整理し、各ページから次に読むページを1つ明示する
2. 「コンセプトダイアグラムとは」と描き方前後編を再編集し、用語、ステップ数、図、例、FAQ間の矛盾をなくす。各記事に「最終レビュー日」と変更概要を表示する
3. FAQは検索流入用に水増しせず、実際の疑問へ短く具体的に答える形式を維持する。重複する質問だけ統合する
4. 現在の手法による新しい公式事例を2〜3件追加する。成果物だけでなく、前提、判断過程、失敗・修正点を含める
5. A4一枚の描き方チェックリストまたはテンプレートを用意し、初訪問者が記事を読んだ後に実践できる状態をつくる
6. Search Consoleの検索語・表示ページを基に、タイトル、概要文、内部リンクを改善する。日付だけを新しく見せる更新は行わず、実質的に変更した記事だけ更新日を変更する
7. Core Web Vitals、モバイル表示、キーボード操作、見出し構造、代替テキスト、色コントラストを監査する。大きな画像のリサイズ・WebP化、不要スクリプト削減、キャッシュ設定を行う
8. 削除前バックアップに含まれる過去の注文・会員情報について保存期限を決め、MySQL移行安定後に安全に廃棄する
9. プライバシーポリシーと実際のGA/GTM設定を照合し、不要なタグ、広告機能、個人識別につながるパラメータ送信がないか確認する

### 長期（3〜12か月）: 単一の公式サイトと持続可能な運用

1. WordPressサイトと旧Gatsby/Netlifyサイトの役割を統合し、`concept-diagram.com`を唯一の正規サイトにする。旧サイトはURL対応表を作って301転送し、重複・壊れた公開先を廃止する
2. 更新頻度が低い状態が続くなら、WordPressを最小構成で維持する案と、静的サイトへ移行する案を比較する。保守費、問い合わせフォーム、検索、リダイレクト、編集方法を基準に判断する
3. 「公式定義」「手順」「FAQ」「事例」の各コンテンツに責任者、レビュー周期、改訂履歴を持たせ、年1回の内容監査と四半期ごとのリンク・更新確認を行う
4. 新規記事数を追わず、独自の知見、実体験、図、検証可能な例を優先する。サイトの主目的をコンセプトダイアグラムの正確な理解と実践支援に固定する
5. GA4では、主要記事の読了、描き方への遷移、テンプレート利用、問い合わせ到達など、サイト目的に直結する少数のイベントだけを計測する。GA・GSCの月次確認項目を定型化する
6. 障害対応手順、復元テスト、管理者権限棚卸し、PHP／MySQL／WordPressの更新方針を文書化し、担当者が変わっても維持できる状態にする

## Netlify公開先について

リポジトリのREADMEに掲載されているNetlifyのURLは応答するが、現在表示される内容は「Kaldi」のGatsbyスターターであり、Concept Diagram公式サイトの内容ではない。

- 確認したURL: https://gatsby-starter-netlify-cms-ci.netlify.app/
- HTTP状態: 200
- 表示内容: Gatsby + Netlify CMS Starter / Kaldi

このURLがConcept Diagram用に所有・設定された正式なNetlifyサイトかは未確認。Netlify管理画面で、サイトID、GitHubリポジトリ連携、独自ドメイン、最新デプロイを確認する必要がある。

## 情報の保管場所

| 情報 | 現在の場所 |
| --- | --- |
| WordPress本番サイト | さくらインターネット |
| WordPress原稿のMarkdown同期 | `concept-diagram.com` リポジトリ |
| Gatsby版サイト | `cdcom-gatsby-netlify` リポジトリ |
| ドメイン・DNS設定 | レジストラはお名前.com、権威DNSはNetlify DNS（NSOne） |
| WordPress管理者・サーバー接続情報 | このファイルには保存しない |
| Netlifyサイト設定 | 未確認 |

## Google Analytics / Search Console

### 利用するサービスアカウント

- メール: `gsheet@python-selenium-280217.iam.gserviceaccount.com`
- Google Cloudプロジェクト: `python-selenium-280217`
- 認証JSON: `/Users/mak/Repos/github_mak00s/poimak4/data/service_account.json`
- JSONのファイル権限: `600`

### Google Analytics

- GAアカウント: `concept-diagram`（アカウントID: `127154402`）
- プロパティ:
  - `Concept-Diagram.com (Netlify)`（プロパティID: `244745069`）
  - `Concept-Diagram.com (Web)`（プロパティID: `206792486`）
  - `Concept-Diagram.com (WordPress) - GA4`（プロパティID: `386697973`）
  - `store.concept-diagram.com - GA4`（プロパティID: `432405879`）
- `mak00s@gmail.com` はGAアカウントの管理者
- サービスアカウントへのGA権限は付与済み
- SAのOAuth認証は成功済み
- Google Cloud側でAnalytics Data APIとSearch Console APIを2026-08-06に有効化し、SAによる実データ取得を確認済み。Analytics Admin APIは未使用・未確認

### GA4 / GTM設定監査（2026-08-05）

#### 公開サイトの実装

- 公開HTMLのGTMコンテナは`GTM-5TDB7H2`のみ。`gtm4wp-options`（Google Tag Manager for WordPress）から挿入され、Google側で有効
- Snow Monkeyの`GTM-59MP4P6`はHTTP 404だったため、2026-08-06に設定を削除済み
- 2026-08-06にWebコンテナを最適化し、公開バージョン5へ更新。Universal Analyticsタグ8件を削除し、公開タグは正規GA4へ直接送信するGoogleタグ1件だけにした
- Webコンテナに残るUniversal Analyticsタグは、`EC - GA`、`Not Found PV - GA`、`お問合せ送信 - GA`、`ログイン - GA`、`会員登録 - GA`、`全ページPV - GA`、`記事精読 - GA`、`離脱リンク - GA`
- 共有サーバーコンテナ`GTM-WMBH6Z6`は公開バージョン43へ更新し、Concept Diagram用の`GA4 for cd`と`GA4 for cd2`を削除。`GA4 for data-ms`、`GA4 for ms`、`GA4 for pt`の別サイト用タグ3件は維持
- `GTM-5TDB7H2`はWebコンテナ`concept-diagram.com (Web)`。公開済みタグは9件で、最終更新はすべて約5年前
- 9件の内訳は、Universal Analyticsタグ8件とGoogleタグ1件
  - 終了済み機能: EC、会員登録、会員ログイン
  - 継続候補: 404、お問い合わせ送信、記事精読、離脱リンク、全ページ
  - ただし継続候補もUniversal Analytics形式のため現在のGA4計測には使えない
- 現在のGoogleタグ`全ページPV - GA4`は測定ID`G-XWKLSJ662E`へ直接送信。`server_container_url`、独自日時パラメータ、Visitor IDのユーザープロパティは削除済み
- `a.concept-diagram.com`は稼働中で、サーバーGTMコンテナ`GTM-WMBH6Z6`へ接続している
- サーバーGTMは`concept-diagram.com`、`makoto-shimizu.com`、`pt9`等で共有され、Concept Diagram向けGA4タグが2件残る。管理画面には「タグ設定サーバーが最新ではない」と警告表示
- 公開後の検証で、配信中の`gtm.js`に`G-XWKLSJ662E`が含まれ、`a.concept-diagram.com`が含まれないことを確認。GA4 Realtime APIでも`page_view`と`session_start`を各1件確認
- Cookie/CMPの表示や、ページ側の明示的なConsent Modeデフォルト設定は確認できず、GTMはページ表示時に直ちに読み込まれる

#### GA4プロパティの分裂

| プロパティ | ID | 測定ID | 監査時の状態 |
| --- | --- | --- | --- |
| Concept-Diagram.com (Web) | `206792486` | `G-ZC8P3LFRY3` | 現行Web GTMの直接送信先。ストリームは48時間以内の受信ありだが、ホームの過去7日集計は0 |
| Concept-Diagram.com (WordPress) - GA4 | `386697973` | `G-XWKLSJ662E` | WordPress用として新設。過去7日でアクティブユーザー6、イベント22、表示回数0。公開Web GTMの測定IDとは不一致 |
| Concept-Diagram.com (Netlify) | `244745069` | 未確認 | 旧Netlify用。現在の正規WordPressサイトの計測先にはしない |
| store.concept-diagram.com - GA4 | `432405879` | 未確認 | 終了済みストア用。履歴参照専用候補 |

- WordPress用GA4の拡張計測は「ページビュー」「サイト内検索」が有効
- Web GA4の拡張計測は「ページビュー」「スクロール」「離脱クリック」ほか3件が有効
- WordPress用GA4とSearch Consoleのリンクは未作成
- サービスアカウントへのGA権限は付与済み。Google CloudプロジェクトでAnalytics Data APIを有効化し、プロパティ`386697973`のレポート取得に成功

#### 推奨する整理方針

1. 正規プロパティを`Concept-Diagram.com (WordPress) - GA4`（`386697973` / `G-XWKLSJ662E`）へ一本化し、切替日を記録する。旧Web、Netlify、Storeプロパティは削除せず履歴参照用にする
2. Web GTMは`GTM-5TDB7H2`を継続利用し、Universal Analyticsタグ8件、EC・会員・ログイン用トリガーと変数を削除する
3. `全ページPV - ssGTM`を、`G-XWKLSJ662E`へ直接送る通常のGoogleタグへ置き換え、`server_container_url`を外す
4. [完了] Snow Monkeyの無効な`GTM-59MP4P6`を削除し、GTM挿入経路をGoogle Tag Manager for WordPressの1系統にした
5. Web GTMからの直接送信を24〜48時間検証後、Concept Diagram向けサーバーGTMタグと`a.concept-diagram.com`の利用を停止する。サーバーコンテナ自体は他サイトと共有のため削除しない
6. 計測イベントをサイト目的に合わせて最小化する
   - `page_view`: GA4標準
   - `select_content`: トップの学習導線クリック。`content_type=learning_path`と項目IDだけを送る
   - `article_engaged`: 主要記事で一定時間かつ一定スクロールを満たした場合に1回
   - `generate_lead`: Contact Form 7の`wpcf7mailsent`時だけ送信。氏名、メール、件名、本文は絶対に送らない
   - `page_not_found`: 404の把握用。キーイベントにはしない
7. キーイベントは当面`generate_lead`だけにし、ページビュー、スクロール、離脱クリックをキーイベントにしない
8. WordPress用GA4にSearch Consoleドメインプロパティをリンクし、検索クエリとランディングページを同じプロパティで確認できるようにする
9. データ保持期間を14か月に設定し、内部トラフィック／開発トラフィックの除外、不要なGoogle Signals・広告パーソナライズ、URLクエリパラメータの除外を確認する
10. Cookie利用方針に合わせ、必要なら同意管理とConsent Mode v2を導入する。少なくとも計測開始条件とオプトアウト方法をプライバシーポリシーと一致させる

### Google Search Console

- 対象プロパティ: `concept-diagram.com`
- `mak00s@gmail.com` でTXTレコードによる所有権確認とドメインプロパティ追加を完了済み
- レジストラはお名前.com、権威DNSはNetlify DNS（`dns1.p05.nsone.net`〜`dns4.p05.nsone.net`）
- サービスアカウント`gsheet@python-selenium-280217.iam.gserviceaccount.com`をフル権限で追加済み
- Google Cloudプロジェクト`python-selenium-280217`でSearch Console APIを有効化し、SAで`sc-domain:concept-diagram.com`を`siteFullUser`として取得、Search Analytics APIのクエリ成功を確認済み
- 2026-08-09にGA4プロパティ`Concept-Diagram.com (WordPress) - GA4`とGSCドメインプロパティ`concept-diagram.com`を本番Webストリームへリンク。ストリームIDは`5505509298`、リンクユーザーは`mak00s@gmail.com`

#### インデックス改善（2026-08-08）

- 本番`.htaccess`へHTTPから正規HTTPS URLへの301転送を追加。トップ、About、描き方記事でHTTP 301とHTTPS 200を確認済み
- 変更前`.htaccess`の退避先: `/home/mak-s/backups/.htaccess.pre-https-20260808`
- Google XML SitemapsのHTMLサイトマップ出力（`sm_b_html`）を停止し、`robots.txt`のSitemap指定を`https://concept-diagram.com/sitemap.xml`だけに整理
- 変更前`sm_options`の退避先: `/home/mak-s/backups/sm_options.pre-html-sitemap-20260808.json`（権限600）
- `sitemap.html`自体はHTTP 200のままだが、robots.txtとXMLサイトマップからは除外済み
- 更新後のXMLサイトマップには、現行のトップ、公開カテゴリー3件、公開投稿11件、固定ページ4件、投稿アーカイブ1件だけが収録されている
- GSCへ`https://concept-diagram.com/sitemap.xml`を再送信し、送信成功を確認
- 2026-08-11のAI一次情報更新後にもサイトマップを再送信。「コンセプトダイアグラムとは」とAboutを優先クロールキューへ追加。基幹記事は登録済み、Aboutは依頼時点で「Googleに認識されていない」状態
- GSCでトップ、「コンセプトダイアグラムとは」、描き方前編、描き方後編、「ステップ数はいくつが適切？」の再クロールを依頼済み
- トップと「ステップ数はいくつが適切？」は依頼時点ですでにインデックス済み。「コンセプトダイアグラムとは」と描き方前後編は「発見済み・未クロール」から優先クロールキューへ追加
- GSCのページレポートは2026-08-05時点のデータで、復旧前のトップ404や閉鎖済みのNetlify・ストア・旧サブドメインの履歴を含む。反映後の件数変化を週次で観測する

### 週次の定点観測（2026-08-06構築）

- Google Sheets: https://docs.google.com/spreadsheets/d/1H-T49FsAWP-s3ObZpD_ezOS8HxCise5XdCKOeV-YVYE
- 集計内容: 過去52週のGA4／GSC推移、直近4週間のページ、検索クエリ、検索LP、クエリ×LP、流入元、実流入を観測した外部参照元URL
- 更新時刻: 毎週月曜09:15 JST（直前の完了済み月〜日週を対象）
- 自動更新コード: `analytics/weekly_report.py`
- GitHub Actions: `.github/workflows/weekly-analytics-report.yml`
- GitHub PR: https://github.com/mak00s/cdcom-gatsby-netlify/pull/43（2026-08-06にmasterへマージ済み）
- 初回GitHub Actions: https://github.com/mak00s/cdcom-gatsby-netlify/actions/runs/31053066413（成功）
- GitHub Secrets: `GOOGLE_SERVICE_ACCOUNT_JSON`、`GOOGLE_SHEET_ID`を登録済み
- 初回取得結果: 直近完了週（2026-07-27〜2026-08-02）はセッション5、ユーザー5。自然検索セッション、GSCクリック、GSC表示回数は0
- 直近4週間でGA4が観測した外部参照元は`makoto-shimizu.com`と`library.musubu.in`。これは実流入ベースであり、網羅的な被リンク索引ではない
- Search Consoleの「リンク」レポートは公式APIの対象外。網羅的なリンク一覧が必要な場合はSearch Console画面から手動エクスポートする
- 現在はGTMのページビュー送信が不完全で、ページ別レポートの表示回数が0の行が多い。正規測定IDへの切替後に回復傾向を判定する

### AI一次情報・可視性改善（2026-08-11）

- 提唱者、定義、目的、構成要素、描き方、顧客状態数、カスタマージャーニーマップとの差を基幹記事へ集約
- 描く作業の手順数と、完成図の顧客状態数（基本5〜6）を区別して明記
- 描き方前後編に、実践例であることと公式FAQ・基幹記事を優先する旨を追記
- Aboutへ運営主体、提唱者、現在の公開範囲、一次情報としての引用方針を追加
- Organization、Person、WebSite、WebPage／BlogPosting、FAQPage等のJSON-LDを追加し、主要URLで構文と型を検証済み
- `https://concept-diagram.com/llms.txt` を追加。公式URL、要点、旧Netlify版を引用しない旨を掲載
- 公開サイト20 URLをクロールし、すべてHTTP 200、実リンク・実画像の内部リンク切れなしを確認。主要画像の代替テキストを補完
- 本番変更前バックアップ: `/home/mak-s/backups/ai-readiness-20260811/database.sql.gz`、`wp-content.tar.gz`（権限600）
- 改修前の同一プロンプト監査では、ChatGPT 5/7、Gemini 5/7、Perplexity 3/7。Claudeは個人アカウント未ログインのため未測定
- 改修直後の監査はChatGPT 5/7、Gemini 5/7、Perplexity 5/7、Claude 7/7。Perplexityは公式サイトの採用が改善した一方、旧個人ブログの「7つ以内」を現行情報と誤認
- AI回答の指摘を受け、基幹記事のCJM比較を2026-08-11に更新。「CJMは現状把握だけ」という二分を廃止し、現代のCJMが将来像、施策、KPI、組織・業務も扱うこと、両手法の差は主な整理軸にあること、併用できることを比較表で明記
- CJM比較更新前バックアップ: `/home/mak-s/backups/concept-diagram-before-cjm-rewrite-20260811.sql`
- FAQ「コンセプトダイアグラムとカスタマージャーニーマップの違いは？」を追加。旧セミナー資料の整理、CJM実務の広がり、現在の公式比較、過去資料を引用する際の注意を説明し、基幹記事から相互リンク。GOV.UKの取得可能な資料でCurrent／To-Beの説明を補強
- CJM FAQ追加前バックアップ: `/home/mak-s/backups/concept-diagram-before-cjm-faq-20260811.sql`
- 構成要素を公式の必須8項目（対象顧客、スタート、ゴール、2つの心理軸、顧客状態、状態変化、施策、評価指標）として固定。従来の5分類は複数要素をまとめた分類だったことを明記
- 「ステップ」は作業手順と紛らわしいため、図中の中間状態を現在の公式用語では「顧客状態」と表記。基本5〜6個はスタートとゴールを除く中間の顧客状態数と明記
- FAQ「コンセプトダイアグラムの構成要素は何ですか？」を追加し、必須／補助の区別、評価指標を追加する時点、8項目の簡略例を掲載。基幹記事、描き方前後編、ステップ数FAQから相互リンク
- 基幹記事と構成要素FAQへ、8項目を順序付きで表す`ItemList` JSON-LDを追加。両ページで8件の`ListItem`を検証済み
- 構成要素仕様更新前バックアップ: `/home/mak-s/backups/concept-diagram-before-elements-spec-20260811.sql`、`/home/mak-s/backups/kosgis-add-function-before-elements-20260811.php`
- 監査記録: `analytics/ai-answer-audit-2026-08-11.md`、機械可読データ: `analytics/ai_answer_benchmark.json`
- Google Sheetsへ`AI Referrals`、`AI Answer Audit`、`AI Crawlers`タブを追加
- 2023年以降のGA4で識別できたAI参照流入はClaudeから1セッション。参照元が送られない流入は含まれない
- さくらの保存ログ（2026-05-11以降）では、2026-08-11時点の公式IP範囲と照合できたOAI-SearchBotを224件確認したが、すべて`/robots.txt`で本文取得は0件
- User-Agentのみの大量アクセスは偽装を含むため、OpenAI、Perplexity、Googleは各社の最新公開IP範囲と照合して集計。AnthropicはIP範囲非公開のためUA候補と明記
- GitHub Actions用に固定ログ出力コマンドしか実行できない専用SSH鍵を登録。秘密鍵と固定ホスト鍵はGitHub Secretsで保管

パスワード、APIキー、秘密鍵などの認証情報は、このリポジトリやMarkdownファイルに記載しない。

## 今後確認する項目

- [ ] さくらインターネットの契約サービス名とサーバー名
- [x] `concept-diagram.com` のDNS管理事業者と権威DNSを確認する
- [ ] WordPressのサイトURL、ホームURL、フロントページ設定
- [x] 有効なWordPressテーマと主要プラグイン
- [x] 有効なプラグインと更新候補の監査
- [ ] 404発生前後の更新・変更履歴
- [x] WordPress本体、テーマ、プラグイン、データベースのバックアップ状況の監査
- [x] PHPおよびWordPressの更新順序案
- [x] Snapupで現在のWordPress全ファイルとDBのスナップショットを取得する
- [x] Snapupで本番複製のステージング環境を用意する
- [x] SnapupステージングのURLと外部認証状態を確認する
- [x] SnapupステージングのPHP・MySQLバージョンを確認する
- [x] 作成済みスナップショットをSnapupステージングへセットする
- [x] SnapupステージングのPHPを少なくとも本番と同じ7.4へ変更する
- [x] ステージングでWordPress 7.0.2とDBを更新する
- [x] ステージングでSnow Monkey、Contact Form 7、SiteGuard等を更新する
- [x] 更新済みステージングからスナップショットを作成する
- [x] PHP 8.1で主要ページ、管理画面、継続プラグインの互換性試験を行う
- [x] 本番更新後にPHP 8.3へ切り替え、主要機能と起動警告を確認する
- [x] MySQL 8.0アップグレードを実施し、`wp-config.php`の接続先を変更して確認する
- [x] 本番の直前DBダンプとWordPress全ファイルアーカイブを取得・検査する
- [x] 本番のWordPress、Snow Monkey、Contact Form 7、SiteGuardを検証済みバージョンへ更新する
- [x] 本番の販売・会員・フォーラム機能を停止し、商品・旧ページを下書き化する
- [ ] ステージングのメール・決済・Webhook・検索エンジン公開を抑止する
- [x] 注文58件と会員108件を運用DBから削除する
- [ ] 削除前バックアップに含まれる個人情報の保存期限を決める
- [x] 本番のStripe決済と在庫あり商品の販売受付を停止する
- [x] ステージングで販売・会員・フォーラム関連プラグインを停止して表示確認する
- [x] 商品・会員・決済ページとメニューを下書き化する
- [ ] Netlify管理画面上の正式なサイト名とサイトID
- [x] 専用公開鍵をさくらサーバーの `mak-s` ユーザーへ登録する
- [ ] Google CloudでAnalytics Admin APIを有効化する（Analytics Data APIとSearch Console APIは有効化済み）
- [x] Search Consoleにドメインプロパティを追加し、TXTレコードで所有権を確認する
- [x] Search Consoleでサービスアカウントへフル権限を付与する
- [ ] 今後の正式サイトをWordPressとGatsbyのどちらに統一するか

## 運用方針（案）

1. まず、さくらインターネット上のWordPressをバックアップする。
2. WordPressの404原因を切り分け、現行サイトを復旧する。
3. 復旧後、WordPressを継続するか、静的サイトへ移行するかを決める。
4. 使用しない公開サイトとデプロイ設定を整理し、公式URLを一つに統一する。
5. 構成や運用先を変更したら、このファイルも更新する。
