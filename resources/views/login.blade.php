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
        <login-form old-email="{{ old('email', '') }}" email-initial-error-msg="{{ $errors->first('email') }}" :old-remember="{{ old('remember') ? 'true' : 'false' }}" email-initial-error-msg="" password-initial-error-msg=""/>
      </div>
    </main>
    @include('include.footer')
    <page-loading />
    <alert-message initial-msg="{{ session('alert.msg') }}" initial-type="{{ session('alert.type') }}" />
  </div>
  @vite('resources/js/app.js')
</body>
</html>
