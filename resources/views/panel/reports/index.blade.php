@extends('layouts.panel')
@section('title', 'Raporlar')
@section('page-title', 'Raporlar')
@section('page-subtitle', 'Performans ve gelir analizleri')

@section('page-actions')
<div style="display:flex; gap:0.4rem;">
    @foreach(['daily'=>'Bugün','weekly'=>'Bu Hafta','monthly'=>'Bu Ay'] as $val=>$label)
    <a href="?period={{ $val }}" style="padding:0.35rem 0.9rem; border-radius:8px; font-size:0.8rem; font-weight:500; text-decoration:none;
        background:{{ $period==$val ? 'var(--color-orange)' : 'var(--color-surface-2)' }};
        color:{{ $period==$val ? 'white' : 'var(--color-muted)' }};
        border:1px solid {{ $period==$val ? 'transparent' : 'var(--color-border)' }};">{{ $label }}</a>
    @endforeach
</div>
@endsection

@section('content')
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div class="card-sm" style="border-left:3px solid var(--color-green);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Toplam Ciro</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ number_format($totalRevenue,0,',','.') }}₺</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-orange);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Toplam Randevu</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ $totalCount }}</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-blue);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Tamamlanan</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ $completedCount }}</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-purple);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">No-Show Oranı</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">%{{ $noShowRate }}</div>
    </div>
</div>

<div class="card" style="text-align:center; padding:3rem; color:var(--color-muted);">
    <div style="font-size:1rem; margin-bottom:0.5rem;">📊</div>
    Daha detaylı grafikler yakında eklenecek.
</div>
@endsection
