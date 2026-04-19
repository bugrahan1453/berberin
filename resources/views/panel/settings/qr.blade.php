@extends('layouts.panel')
@section('title', 'QR Kod')
@section('page-title', 'QR Kod')
@section('page-subtitle', 'Dükkan girişine asın')

@section('content')
<div style="display:flex; justify-content:center;">
<div class="card" style="max-width:480px; width:100%; text-align:center;">
    <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">{{ $shop->name }}</h3>
    <p style="font-size:0.8rem; color:var(--color-muted); margin-bottom:1.5rem;">Bu QR kodu tarararak müşterileriniz anlık doluluk durumunuzu görebilir ve randevu alabilir.</p>

    <div style="background:white; padding:1rem; border-radius:12px; display:inline-block; margin-bottom:1.5rem;">
        <img src="{{ $qrUrl }}" alt="QR Kod" style="width:200px; height:200px;">
    </div>

    <div style="background:var(--color-surface-2); border-radius:8px; padding:0.75rem; margin-bottom:1.5rem;">
        <div style="font-size:0.7rem; color:var(--color-muted); margin-bottom:0.25rem;">Dükkan URL'i</div>
        <div style="font-size:0.8rem; color:var(--color-orange); word-break:break-all;">{{ $shopUrl }}</div>
    </div>

    <div style="display:flex; gap:0.75rem; justify-content:center;">
        <a href="{{ $qrUrl }}" download="qr-{{ $shop->slug }}.png" class="btn-primary">
            QR İndir
        </a>
        <button onclick="navigator.clipboard.writeText('{{ $shopUrl }}').then(()=>alert('Kopyalandı!'))" class="btn-secondary">
            Linki Kopyala
        </button>
    </div>
</div>
</div>
@endsection
