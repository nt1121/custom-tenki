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
                <h1 class="c-page-heading">パスワードの再設定</h1>
                <p>
                    メールアドレス宛に確認URLを記載したメールを送信いたしました。<br>
                    メールが送信されない場合は、入力いただいたメールアドレスが登録されていないか、会員登録が完了していない可能性がございます。
                </p>
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app.js')
</body>

</html>
