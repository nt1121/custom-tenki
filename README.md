# CustomTenki

## 概要

表示する項目がカスタマイズできる天気予報Webアプリケーションです。

## 使用技術

Laravel 10、Vue 3等を使用して作成しました。

## 利用方法

新規会員登録画面でメールアドレスとパスワードを入力して会員登録後、メールアドレス宛に送信される確認URLにアクセスすることによりログイン可能になります。  
地域の選択画面で天気予報を表示したい地域を選択すると、ホーム画面に３時間毎の天気予報が３日後まで表示されます。  
項目の選択画面でホーム画面に表示する項目と表示順を設定できます。   
設定画面からメールアドレスの変更、パスワードの変更、アカウントの削除が可能です。

## 機能一覧

- 新規会員登録機能
- ログイン機能
- ログアウト機能
- パスワード再設定機能
- テストユーザーログイン機能（有効になっている場合）
- ３時間毎の天気情報を３日後まで表示する機能
- 地域の選択機能
- 表示する項目の選択と表示順の設定機能
- メールアドレス変更機能
- パスワード変更機能
- アカウント削除機能

## データベース設計

[ER図](etc/er_diagram.png)

## 動作確認済み環境

OS: macOS Monterey, CentOS 7.9.2009  
npm: 7.15.1  
Node.js: 16.3.0  
PHP: 8.1.22, 8.2.0  
Composer: 2.5.8  
DB: 5.7.39 - MySQL Community Server (GPL), 5.5.68-MariaDB - MariaDB Server

## インストール方法

このアプリケーションを動作させるためにはPHPのバージョンが8.1以上である必要があります。

サーバーにインストールする場合はドキュメントルート配下以外で下記を実行します。

```
git clone https://github.com/nt1121/custom-tenki.git
```

作成されたcustom-tenkiディレクトリに移動します。  
.env.exampleをコピーし、.envにリネームします。

.envをエディタで開き、必要な項目を設定します。

```
APP_NAME=CustomTenki  
APP_URL=（トップページのURL）  
LOG_CHANNEL=daily

DB_CONNECTION=（mysqlなど）  
DB_HOST=（データベースのIPアドレス）  
DB_PORT=（データベースのポート番号）  
DB_DATABASE=（このアプリーケーションで使用するデータベース名）  
DB_USERNAME=（データベースのユーザー名）  
DB_PASSWORD=（データベースのユーザーのパスワード）
```

下記についてはご自身のSMTPサーバーなどの情報を入力してください。

```
MAIL_MAILER  
MAIL_HOST  
MAIL_PORT  
MAIL_USERNAME  
MAIL_PASSWORD  
MAIL_ENCRYPTION  
MAIL_FROM_ADDRESS
```

下記を追記してください。

```
WEATHER_API_KEY=（https://openweathermap.org/ にて会員登録を行い、APIキーを取得して、こちらに設定してください）  
SYSTEM_ADMIN_EMAIL_ADDRESS=（バッチ処理失敗時にメール通知を行いたい場合は、送信先メールアドレスをこちらに設定してください）
```

.envの変更を保存します。

下記のコマンドを上から順に実行します。

```
composer install
npm install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build 
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

不要データを削除するバッチ処理を定期的に実行するため、 `crontab -e` を実行して下記を追記します。  

```
* * * * * cd （アプリケーションのルートディレクトリの絶対パスをこちらに設定してください） && php artisan schedule:run >> /dev/null 2>&1
```



