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
                <p>{{ print_r(session('alert'), true) }}</p>
                <login-form old-email="{{ old('email', '') }}" email-initial-error-msg="{{ $errors->first('email') }}"
                    :old-remember="{{ old('remember') ? 'true' : 'false' }}" email-initial-error-msg=""
                    password-initial-error-msg=""></login-form>
            </div>
        </main>
        @include('include.footer')
        <alert-message initial-msg="{{ strval(session('alert.msg')) }}"
            initial-type="{{ strval(session('alert.type')) }}"></alert-message>
        <page-loading></page-loading>
    </div>
    @vite('resources/js/app.js')
</body>

</html>
