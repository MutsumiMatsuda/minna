# Afosto Acmeによる動的SSL証明書取得/反映テストプロジェクト

## 環境変数に以下の設定を追加して下さい。

### ----------------------------------------------------
### ACME関連環境変数
### ----------------------------------------------------
### 証明書、キー保存ベースディレクトリ
ACME_FILES_BASE_DIR="/home/user/webroot/minna/storage/acme/"

### ACME証明書ファイル名(全ドメイン共通)
ACME_CERT_FNAME="certificate.cert"

### ACMEプライベートキーファイル名(全ドメイン共通)
ACME_KEY_FNAME="private.key"

### ACMEチャレンジ用proxy設定
### proxyを使用しない場合は、空文字に設定して下さい
ACME_PROXY="url:port"

### チャレンジ用メールアドレス
ACME_MAIL_ADDRESS="your@mail.address"

### 登録ドメイン用Nginx設定ファイルベースディレクトリ
NGINX_CONF_BASE_DIR="/home/user/webroot/minna/storage/nginx/"

### http接続用Nginx設定ファイルテンプレート
NGINX_CONF_TEMPLATE_NOSSL="${NGINX_CONF_BASE_DIR}server_tpl_nossl"

### SSL接続用Nginx設定ファイルテンプレート
NGINX_CONF_TEMPLATE_SSL="${NGINX_CONF_BASE_DIR}server_tpl_ssl"

### 登録ドメイン設定ファイル保存ディレクトリ(含無効ドメイン)
NGINX_CONF_AVAILABLE_DIR="${NGINX_CONF_BASE_DIR}sites-available/"

### 有効登録ドメインの設定ファイル保存ディレクトリ
NGINX_CONF_ENABLED_DIR="${NGINX_CONF_BASE_DIR}sites-enabled/"

### アプリケーションルートディレクトリ
APP_ROOT="/home/user/webroot/minna/public"

### php-fpmソケットファイルパス
FPM_SOCKET="/home/user/webroot/run/php-fpm/php8.0.24-fpm.sock"
