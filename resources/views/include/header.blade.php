<header class="l-header">
    <div class="p-header">
        <div class="p-header__inner">
            <div class="p-header__logo"><a href="/" class="p-header__logo-link">CustomTenki</a></div>
            <nav class="p-header__nav">
                <ul class="p-header__nav-list">
                    @if (auth()->check())
                    <li class="p-header__nav-list-item">
                        <a class="p-header__nav-list-item-link" href="{{ !empty($isWeather) ? '/weather' : '/' }}">ホーム</a>
                    </li>
                    <li class="p-header__nav-list-item">
                        <a class="p-header__nav-list-item-link" href="/weather/settings">設定</a>
                    </li>
                    <li class="p-header__nav-list-item">
                        <form action="/logout" method="post">
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
                <div class="p-header__hamburger-button-line1" :class="{ 'p-header__hamburger-button-line1--active': isHamburgerMenuActive }"></div>
                <div class="p-header__hamburger-button-line2" :class="{ 'p-header__hamburger-button-line2--active': isHamburgerMenuActive }"></div>
                <div class="p-header__hamburger-button-line3" :class="{ 'p-header__hamburger-button-line3--active': isHamburgerMenuActive }"></div>
            </div>
        </div>
    </div>
</header>
