<header class="l-header">
    <div class="p-header">
        <div class="p-header__inner">
            <div class="p-header__logo">
                @if (!empty($isWeather))
                    <router-link to="/weather" class="p-header__logo-link">CustomTenki</router-link>
                @elseif (auth()->check())
                    <a href="/weather" class="p-header__logo-link">CustomTenki</a>
                @else
                    <a href="/" class="p-header__logo-link">CustomTenki</a>
                @endif
            </div>
            <nav class="p-header__nav">
                <ul class="p-header__nav-list">
                    @if (!empty($isWeather))
                        <li class="p-header__nav-list-item">
                            <router-link class="p-header__nav-list-item-link" to="/weather">ホーム</router-link>
                        </li>
                        <li class="p-header__nav-list-item">
                            <router-link class="p-header__nav-list-item-link" to="/weather/settings">設定</router-link>
                        </li>
                        <li class="p-header__nav-list-item">
                            <form action="/logout" method="POST" @submit="showPageLoading">
                                @csrf
                                <button type="submit" class="p-header__nav-list-item-link">ログアウト</button>
                            </form>
                        </li>
                    @elseif (auth()->check())
                        <li class="p-header__nav-list-item">
                            <a class="p-header__nav-list-item-link" href="/weather">ホーム</a>
                        </li>
                        <li class="p-header__nav-list-item">
                            <a class="p-header__nav-list-item-link" href="/weather/settings">設定</a>
                        </li>
                        <li class="p-header__nav-list-item">
                            <form action="/logout" method="POST">
                                @csrf
                                <button type="submit" class="p-header__nav-list-item-link">ログアウト</button>
                            </form>
                        </li>
                    @else
                        <li class="p-header__nav-list-item">
                            <a class="p-header__nav-list-item-link" href="/login">ログイン</a>
                        </li>
                        <li class="p-header__nav-list-item">
                            <a class="p-header__nav-list-item-link" href="/register">新規会員登録</a>
                        </li>
                    @endif
                </ul>
            </nav>
            <div class="p-header__hamburger-button" @click="toggleHamburgerMenu">
                <div class="p-header__hamburger-button-line1"
                    :class="{ 'p-header__hamburger-button-line1--active': isHamburgerMenuActive }"></div>
                <div class="p-header__hamburger-button-line2"
                    :class="{ 'p-header__hamburger-button-line2--active': isHamburgerMenuActive }"></div>
                <div class="p-header__hamburger-button-line3"
                    :class="{ 'p-header__hamburger-button-line3--active': isHamburgerMenuActive }"></div>
            </div>
        </div>
    </div>
</header>
