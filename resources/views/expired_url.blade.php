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
                <h1 class="c-page-heading">無効なURL</h1>
                <p>URLの有効期限が切れています。</p>
            </div>
        </main>
        @include('include.footer')
        <alert-message initial-msg="{{ strval(session('alert.msg')) }}"
            initial-type="{{ strval(session('alert.type')) }}"></alert-message>
    </div>
    @vite('resources/js/app_non_spa.js')
</body>

</html>
