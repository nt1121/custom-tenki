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
                <h1 class="c-page-heading">メールアドレスの変更</h1>
                <p class="u-mb-20">メールアドレスが変更できませんでした。</p>
            </div>
        </main>
        @include('include.footer')
    </div>
    @vite('resources/js/app_non_spa.js')
</body>

</html>
