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
                <p>会員登録が完了できませんでした。</p>
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app.js')
</body>

</html>
