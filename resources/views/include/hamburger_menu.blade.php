<Transition name="u-fade-hamburger-menu">
    <div class="p-hamburger-menu" v-show="isHamburgerMenuActive">
        @if (auth()->check())
            <a href="/weather" class="p-hamburger-menu__link">ホーム</a>
            <a href="/weather/settings" class="p-hamburger-menu__link">設定</a>
            <form action="/logout" method="post">
                @csrf
                <button type="submit" class="p-hamburger-menu__link">ログアウト</button>
            </form>
        @else
            <a href="/login" class="p-hamburger-menu__link">ログイン</a>
            <a href="/register" class="p-hamburger-menu__link">新規会員登録</a>
        @endif
    </div>
</Transition>