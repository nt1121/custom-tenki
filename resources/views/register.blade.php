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
                    password-initial-error-msg="{{ $errors->first('password') }}" />
            </div>
        </main>
        @include('include.footer')
        <page-loading />
        <alert-message initial-msg="{{ session('alert.msg') }}" initial-type="{{ session('alert.type') }}" />
    </div>
    @vite('resources/js/app.js')
</body>

</html>
