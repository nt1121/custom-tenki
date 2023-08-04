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
                @if (!empty($isPasswordReset))
                <h1 class="c-page-heading">パスワードの再設定</h1>
                <p>
                    現在別の会員でログイン中です。<br>
                    パスワードの再設定を行う場合は一度ログアウトしてからこのURLにもう一度アクセスしてください。
                </p>
                @endif
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app.js')
</body>

</html>
