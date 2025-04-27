<header class="header">
    <div class="header__container">
        <!-- ロゴ部分（常に表示） -->
        <div class="header__logo">
            <a href="{{ route('products.index') }}">
                <img src="/images/logo.png" alt="COACHTECH" class="header__logo-image">
            </a>
        </div>
        <!-- 検索バー（ログイン・会員登録ページでは非表示） -->
        @if(!request()->routeIs('login') && !request()->routeIs('register'))
        <div class="header__search">
            <form action="{{ route('products.index') }}" method="GET" class="header__search-form">
                <input type="text" name="search" placeholder="なにをお探してすか？"
                        value="{{ request('search') }}" class="header__search-input">
                <input type="hidden" name="tab" value="{{ request('tab', 'recommended') }}">
                <button type="submit" class="header__search-button">検索</button>
            </form>
        </div>
        @endif

        <!-- ナビゲーション（ログイン・会員登録ページでは非表示） -->
        @if(!request()->routeIs('login') && !request()->routeIs('register'))
            <nav class="header__nav">
                @auth
                    <!-- ログイン済みの場合 -->
                    <a href="{{ route('logout') }}" class="header__nav-link"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        ログアウト
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf <!---->
                    </form>
                @else
                    <!-- 未ログインの場合 -->
                    <a href="{{ route('login') }}" class="header__nav-link">ログイン</a>
                @endauth
                <a href="{{ route('mypage') }}" class="header__nav-link">マイページ</a>
                <a href="{{ route('products.create') }}" class="header__nav-button">出品</a>
            </nav>
        @endif
    </div>
</header>