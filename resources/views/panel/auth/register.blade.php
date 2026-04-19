@extends('layouts.auth')
@section('title', 'Kayıt Ol')

@section('content')
<h2 style="font-family:'Outfit',sans-serif; font-size:1.3rem; font-weight:700; margin-bottom:0.25rem;">Dükkan Kaydı</h2>
<p style="font-size:0.8rem; color:var(--color-muted); margin-bottom:1.5rem;">Yeni hesap oluşturun</p>

@if($errors->any())
<div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:8px; padding:0.75rem; margin-bottom:1rem; color:#EF4444; font-size:0.8rem;">
    {{ $errors->first() }}
</div>
@endif

<form method="POST" action="{{ route('panel.register.post') }}">
    @csrf
    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Ad Soyad</label>
        <input type="text" name="name" value="{{ old('name') }}" class="input" placeholder="Ahmet Yılmaz" required>
    </div>
    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Telefon Numarası</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" class="input" placeholder="05xx xxx xx xx" required>
    </div>
    <div style="margin-bottom:1rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Cinsiyet</label>
        <select name="gender" class="input" required>
            <option value="">Seçin...</option>
            <option value="male" {{ old('gender')=='male'?'selected':'' }}>Erkek</option>
            <option value="female" {{ old('gender')=='female'?'selected':'' }}>Kadın</option>
        </select>
    </div>
    <div style="margin-bottom:1.5rem;">
        <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; color:var(--color-muted);">Şifre</label>
        <input type="password" name="password" class="input" placeholder="En az 6 karakter" required>
    </div>
    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:0.75rem;">
        Hesap Oluştur
    </button>
</form>

<p style="text-align:center; margin-top:1.25rem; font-size:0.8rem; color:var(--color-muted);">
    Zaten hesabınız var mı? <a href="{{ route('panel.login') }}" style="color:var(--color-orange); font-weight:600;">Giriş Yap</a>
</p>
@endsection
