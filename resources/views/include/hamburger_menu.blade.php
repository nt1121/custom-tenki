<Transition name="u-fade-hamburger-menu">
    <div class="p-hamburger-menu" v-show="isHamburgerMenuActive">
        @if (!empty($isWeather))
            <router-link class="p-hamburger-menu__link" to="/weather" @click="toggleHamburgerMenu">ホーム</router-link>
            <router-link class="p-hamburger-menu__link" to="/weather/settings" @click="toggleHamburgerMenu">設定</router-link>
            <form action="/logout" method="POST" @submit="showPageLoading">
                @csrf
                <button type="submit" class="p-hamburger-menu__link">ログアウト</button>
            </form>
        @elseif (auth()->check())
            <a href="/weather" class="p-hamburger-menu__link">ホーム</a>
            <a href="/weather/settings" class="p-hamburger-menu__link">設定</a>
            <form action="/logout" method="POST" @submit="showPageLoading">
                @csrf
                <button type="submit" class="p-hamburger-menu__link">ログアウト</button>
            </form>
        @else
            <a href="/login" class="p-hamburger-menu__link">ログイン</a>
            <a href="/register" class="p-hamburger-menu__link">新規会員登録</a>
        @endif
    </div>
</Transition>
