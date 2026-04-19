<!DOCTYPE html>
<html lang="tr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — BERBERiN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.x/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#0A0A0F; color:#F1F1F3;">

<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="sidebar" x-data="{ open: true }">
        <!-- Logo -->
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--color-border);">
            <div style="font-family:'Outfit',sans-serif; font-size:1.35rem; font-weight:700; color:var(--color-orange);">
                ✂ BERBERiN
            </div>
            <div style="font-size:0.7rem; color:var(--color-muted); margin-top:2px;">Dükkan Yönetim Paneli</div>
        </div>

        <!-- Nav -->
        <nav style="flex:1; padding:0.75rem 0; overflow-y:auto;">
            <a href="{{ route('panel.dashboard') }}" class="sidebar-link {{ request()->routeIs('panel.dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('panel.appointments') }}" class="sidebar-link {{ request()->routeIs('panel.appointments*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Randevular
            </a>
            <a href="{{ route('panel.seats') }}" class="sidebar-link {{ request()->routeIs('panel.seats*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 9V5a2 2 0 012-2h10a2 2 0 012 2v4"/><path d="M3 13h18v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5z"/><path d="M8 21v-8"/><path d="M16 21v-8"/></svg>
                Koltuklar
            </a>
            <a href="{{ route('panel.staff') }}" class="sidebar-link {{ request()->routeIs('panel.staff*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M21 21v-2a4 4 0 00-3-3.85"/></svg>
                Personel
            </a>
            <a href="{{ route('panel.services') }}" class="sidebar-link {{ request()->routeIs('panel.services*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                Hizmetler
            </a>

            <div style="padding:0.5rem 1.75rem; font-size:0.65rem; color:var(--color-muted); font-weight:600; letter-spacing:.08em; text-transform:uppercase; margin-top:0.5rem;">Yönetim</div>

            <a href="{{ route('panel.gallery') }}" class="sidebar-link {{ request()->routeIs('panel.gallery*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Galeri
            </a>
            <a href="{{ route('panel.reviews') }}" class="sidebar-link {{ request()->routeIs('panel.reviews*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Yorumlar
            </a>
            <a href="{{ route('panel.reports') }}" class="sidebar-link {{ request()->routeIs('panel.reports*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Raporlar
            </a>
            <a href="{{ route('panel.settings') }}" class="sidebar-link {{ request()->routeIs('panel.settings*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                Ayarlar
            </a>
        </nav>

        <!-- Alt bilgi -->
        <div style="padding:1rem 1.25rem; border-top:1px solid var(--color-border);">
            <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--color-orange);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;">{{ auth()->user()->name ?? '' }}</div>
                    <div style="font-size:0.65rem;color:var(--color-muted);">Dükkan Sahibi</div>
                </div>
            </div>
            <form method="POST" action="{{ route('panel.logout') }}">
                @csrf
                <button type="submit" style="width:100%;text-align:left;display:flex;align-items:center;gap:0.5rem;color:var(--color-muted);font-size:0.8rem;padding:0.4rem 0.5rem;border-radius:8px;border:none;background:none;cursor:pointer;" onmouseover="this.style.color='var(--color-red)'" onmouseout="this.style.color='var(--color-muted)'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Çıkış Yap
                </button>
            </form>
        </div>
    </aside>

    <!-- Ana içerik -->
    <main style="margin-left:240px; flex:1; padding:1.5rem 2rem; min-height:100vh;">
        <!-- Üst bar -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div>
                <h1 style="font-family:'Outfit',sans-serif; font-size:1.4rem; font-weight:700;">@yield('page-title')</h1>
                <p style="font-size:0.8rem; color:var(--color-muted); margin-top:2px;">@yield('page-subtitle')</p>
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                @yield('page-actions')
            </div>
        </div>

        <!-- Flash mesajlar -->
        @if(session('success'))
        <div style="background:rgba(45,212,160,0.12); border:1px solid rgba(45,212,160,0.3); border-radius:10px; padding:0.75rem 1rem; margin-bottom:1rem; color:#2DD4A0; font-size:0.875rem; display:flex; align-items:center; gap:0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); border-radius:10px; padding:0.75rem 1rem; margin-bottom:1rem; color:#EF4444; font-size:0.875rem; display:flex; align-items:center; gap:0.5rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>
