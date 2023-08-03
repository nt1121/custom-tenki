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
                <h1 class="c-page-heading">無効なURL</h1>
                <p>URLの有効期限が切れています。</p>
            </div>
        </main>
        @include('include.footer')
        <alert-message initial-msg="{{ session('alert.msg') }}" initial-type="{{ session('alert.type') }}" />
    </div>
    @vite('resources/js/app.js')
</body>

</html>
