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
                <password-reset password-initial-error-msg="{{ isset($errors) ? $errors->first('password') : '' }}" token="{{ $token }}"></password-reset>
            </div>
        </main>
        @include('include.footer')
        <alert-message initial-msg="{{ strval(session('alert.msg')) }}"
            initial-type="{{ strval(session('alert.type')) }}"></alert-message>
        <page-loading></page-loading>
    </div>
    @vite('resources/js/app_non_spa.js')
</body>

</html>
