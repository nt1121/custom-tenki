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
                <register-form old-email="{{ old('email', '') }}"
                    :old-agree-to-terms-of-use="{{ old('agree_to_terms_of_use') ? 'true' : 'false' }}"
                    :old-consent-to-privacy-policy="{{ old('consent_to_privacy_policy') ? 'true' : 'false' }}"
                    email-initial-error-msg="{{ $errors->first('email') }}"
                    password-initial-error-msg="{{ $errors->first('password') }}"></register-form>
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
