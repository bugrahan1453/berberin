@extends('layouts.auth')
@section('title', 'Giriş Yap')

@section('content')
<h2 style="font-family:'Outfit',sans-serif; font-size:1.3rem; font-weight:700; margin-bottom:0.25rem;">Hoş Geldiniz</h2>
<p style="font-size:0.8rem; color:var(--color-muted); margin-bottom:1.5rem;">Dükkan panelinize giriş yapın</p>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:8px; padding:0.75rem; margin-bottom:1rem; color:#EF4444; font-size:0.8rem;">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('panel.login.post') }}">
    @csrf
    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Telefon Numarası</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" class="input" placeholder="05xx xxx xx xx" required autofocus>
    </div>
    <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Şifre</label>
        <input type="password" name="password" class="input" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:0.75rem;">
        Giriş Yap
    </button>
</form>

<p style="text-align:center; margin-top:1.25rem; font-size:0.8rem; color:var(--color-muted);">
    Hesabınız yok mu? <a href="{{ route('panel.register') }}" style="color:var(--color-orange); font-weight:600;">Kayıt Ol</a>
</p>
@endsection
