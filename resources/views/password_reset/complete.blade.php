<!DOCTYPE html>
<html lang="ja">

<head>
    @include('include.head')
</head>

<body>
    <div class="l-wrapper" id="app">
        @include('include.header')
        @include('include.hamburger_menu')
        <main class="l-main">
            <div class="l-main__inner">
                <h1 class="c-page-heading">パスワードの再設定</h1>
                <p class="u-mb-20">パスワードの再設定が完了しました。</p>
                @if (auth()->check())
                <a href="/weather" class="c-button c-button--primary">ホームへ</a>
                @else
                <a href="/login" class="c-button c-button--primary">ログイン</a>
                @endif
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app_non_spa.js')
</body>

</html>
