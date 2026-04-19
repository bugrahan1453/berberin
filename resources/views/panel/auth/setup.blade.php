@extends('layouts.auth')
@section('title', 'Dükkan Kur')

@section('content')
<div style="text-align:center; margin-bottom:2rem;">
    <div style="font-size:1.5rem; font-weight:800; color:var(--color-orange); letter-spacing:-0.5px;">BERBERiN</div>
    <div style="font-size:0.875rem; color:var(--color-muted); margin-top:0.25rem;">Dükkanınızı ayarlayın</div>
</div>

@if(session('error'))
<div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#EF4444; padding:0.75rem 1rem; border-radius:8px; font-size:0.875rem; margin-bottom:1rem;">
    {{ session('error') }}
</div>
@endif

<form method="POST" action="{{ route('panel.setup.post') }}">
    @csrf
    <div style="margin-bottom:1rem;">
        <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Dükkan Adı *</label>
        <input type="text" name="name" class="input" placeholder="Ustam Berber" value="{{ old('name') }}" required>
        @error('name')<div style="color:#EF4444;font-size:0.75rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
    </div>
    <div style="margin-bottom:1rem;">
        <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Adres *</label>
        <textarea name="address" class="input" rows="2" placeholder="Örn: Atatürk Cad. No:12" required>{{ old('address') }}</textarea>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
        <div>
            <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Şehir</label>
            <input type="text" name="city" class="input" placeholder="Kocaeli" value="{{ old('city') }}">
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">İlçe</label>
            <input type="text" name="district" class="input" placeholder="İzmit" value="{{ old('district') }}">
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
        <div>
            <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Enlem (Lat)</label>
            <input type="number" name="lat" class="input" step="any" placeholder="40.7765" value="{{ old('lat') }}" required>
        </div>
        <div>
            <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Boylam (Lng)</label>
            <input type="number" name="lng" class="input" step="any" placeholder="29.9187" value="{{ old('lng') }}" required>
        </div>
    </div>
    <div style="margin-bottom:1rem;">
        <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Cinsiyet Filtresi</label>
        <select name="gender_filter" class="input">
            <option value="both">Hepsi (Erkek & Kadın)</option>
            <option value="male">Sadece Erkek</option>
            <option value="female">Sadece Kadın</option>
        </select>
    </div>
    <button type="submit" class="btn-primary" style="width:100%; justify-content:center; margin-top:0.5rem;">Dükkanı Oluştur</button>
</form>
@endsection
