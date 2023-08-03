<!DOCTYPE html>
<html lang="ja">

<head>
    @include('include.head')
</head>

<body>
    <div class="l-wrapper" id="app">
        @include('include.header')
        @include('include.hamburger_menu')
        <p>{{ $errors->first('password') }}</p>
        <main class="l-main">
            <div class="l-main__inner">
                <h1 class="c-page-heading">新規会員登録</h1>
                <p>
                    メールアドレス宛に確認URLを記載したメールを送信いたしました。<br>
                    確認URLへのアクセス後からメールアドレスとパスワードによるログインが可能になります。<br>
                    有効期限までに確認URLへのアクセスがない場合、一定時間経過後に入力いただいた会員情報は削除されます。
                </p>
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app.js')
</body>

</html>
