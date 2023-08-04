<!DOCTYPE html>
<html lang="ja">

<head>
    @include('include.head')
</head>

<body>
    <div class="l-wrapper" id="app">
        <div class="p-top">
            <div class="p-top__cover">
                <div class="p-top__cover-inner">
                    <h1 class="p-top__site-name">CustomTenki</h1>
                    <p class="p-top__site-description">表示する項目などがカスタマイズできる天気予報です。</p>
                    <form action="" method="post" class="p-top__button-wrapper">
                        <button type="submit" class="c-button c-button--primary p-top__button">テストユーザーとしてログイン</button>
                    </form>
                    <div class="p-top__button-wrapper">
                        <a href="/login" class="c-button c-button--primary p-top__button">ログイン</a>
                    </div>
                    <div class="p-top__button-wrapper u-mb-0">
                        <a href="/register" class="c-button c-button--primary p-top__button">新規会員登録</a>
                    </div>
                </div>
            </div>
        </div>
        @include('include.footer', ['isTopPage' => true])
        <alert-message initial-msg="{{ strval(session('alert.msg')) }}"
            initial-type="{{ strval(session('alert.type')) }}"></alert-message>
        <page-loading></page-loading>
    </div>
    @vite('resources/js/app.js')
</body>

</html>
