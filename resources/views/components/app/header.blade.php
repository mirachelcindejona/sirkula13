<header class="app-header">
    <div class="header-inner">
        <button class="icon-button mobile-menu-button" type="button" data-drawer-open aria-label="Buka menu"><x-icon name="menu" /></button>
        <a class="brand" href="{{ route(\App\Support\Home::routeName(auth()->user())) }}" aria-label="SIRKULA-CERDAS beranda">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SIRKULA-CERDAS">
            <span class="brand-copy"><span class="brand-name">SIRKULA</span><span class="brand-sub">CERDAS · RW 13</span></span>
        </a>
        <div class="header-spacer"></div>
        <x-notice-bell />
        <div class="account-chip">
            <span class="account-chip-name">{{ auth()->user()->name }}</span>
            <span class="account-chip-role">{{ auth()->user()->primaryRoleName() }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" data-confirm="Anda akan keluar dari akun ini. Lanjutkan?">
            @csrf
            <button class="btn outline logout-button" type="submit"><x-icon name="user" /> Keluar</button>
        </form>
    </div>
</header>
