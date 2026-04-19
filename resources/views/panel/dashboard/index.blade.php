@extends('layouts.panel')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Bugün — ' . \Carbon\Carbon::today()->locale('tr')->isoFormat('D MMMM YYYY, dddd'))

@section('page-actions')
<a href="{{ route('panel.appointments') }}" class="btn-primary">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Randevu Ekle
</a>
@endsection

@section('content')
<!-- Özet kartlar -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; margin-bottom:1.5rem;">
    <div class="card-sm" style="border-left:3px solid var(--color-orange);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Bugünkü Randevular</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ $todayAppointments }}</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-green);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Günlük Ciro</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ number_format($todayRevenue, 0, ',', '.') }}₺</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-gold);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Bekleyen Onay</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">{{ $pendingCount }}</div>
    </div>
    <div class="card-sm" style="border-left:3px solid var(--color-blue);">
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.4rem;">Doluluk Oranı</div>
        <div style="font-size:1.75rem; font-weight:700; font-family:'Outfit',sans-serif;">%{{ $occupancyRate }}</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem; margin-bottom:1.5rem;">
    <!-- Grafik -->
    <div class="card">
        <div style="font-size:0.9rem; font-weight:600; margin-bottom:1rem;">Son 7 Gün</div>
        <canvas id="weeklyChart" height="100"></canvas>
    </div>

    <!-- Koltuk durumları -->
    <div class="card">
        <div style="font-size:0.9rem; font-weight:600; margin-bottom:1rem;">Koltuk Durumu</div>
        @forelse($shop->seats as $seat)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid var(--color-border);">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $seat->status==='empty'?'#2DD4A0':($seat->status==='busy'?'#EF4444':($seat->status==='reserved'?'#F5C842':'#8B8B9E')) }};"></div>
                <span style="font-size:0.85rem;">{{ $seat->name }}</span>
                @if($seat->is_vip) <span style="font-size:0.65rem;color:#F5C842;">VIP</span> @endif
            </div>
            <span class="badge-{{ $seat->status }} badge-status">
                {{ ['empty'=>'Boş','busy'=>'Dolu','reserved'=>'Rezerve','inactive'=>'Pasif'][$seat->status] }}
            </span>
        </div>
        @empty
        <p style="color:var(--color-muted);font-size:0.8rem;text-align:center;padding:1rem 0;">Henüz koltuk eklenmedi.</p>
        @endforelse
        <a href="{{ route('panel.seats') }}" style="display:block; text-align:center; margin-top:0.75rem; font-size:0.8rem; color:var(--color-orange);">Yönet →</a>
    </div>
</div>

<!-- Bugünkü randevular -->
<div class="card">
    <div style="font-size:0.9rem; font-weight:600; margin-bottom:1rem;">Bugünkü Randevular</div>
    @forelse($upcomingAppointments as $appt)
    <div style="display:grid; grid-template-columns:70px 1fr auto; gap:0.75rem; align-items:center; padding:0.65rem 0; border-bottom:1px solid var(--color-border);">
        <div style="text-align:center; background:var(--color-surface-3); border-radius:8px; padding:0.35rem;">
            <div style="font-size:1rem; font-weight:700; color:var(--color-orange);">{{ $appt->time }}</div>
        </div>
        <div>
            <div style="font-size:0.875rem; font-weight:600;">{{ $appt->customer_name ?? $appt->user?->name ?? 'Walk-in' }}</div>
            <div style="font-size:0.75rem; color:var(--color-muted);">{{ $appt->service?->name }} • {{ $appt->staff?->name ?? '—' }}</div>
        </div>
        <span class="badge-{{ $appt->status }} badge-status">
            {{ ['pending'=>'Bekliyor','confirmed'=>'Onaylı','in_progress'=>'İşlemde','completed'=>'Bitti','cancelled'=>'İptal','no_show'=>'Gelmedi'][$appt->status] }}
        </span>
    </div>
    @empty
    <p style="color:var(--color-muted);font-size:0.85rem;text-align:center;padding:1.5rem 0;">Bugün için randevu yok.</p>
    @endforelse
</div>

<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($weeklyLabels),
        datasets: [{
            label: 'Randevu',
            data: @json($weeklyAppointments),
            backgroundColor: 'rgba(255,107,53,0.7)',
            borderRadius: 6,
            yAxisID: 'y',
        },{
            label: 'Ciro (₺)',
            data: @json($weeklyRevenue),
            type: 'line',
            borderColor: '#2DD4A0',
            backgroundColor: 'rgba(45,212,160,0.1)',
            borderWidth: 2,
            pointRadius: 4,
            tension: 0.4,
            yAxisID: 'y1',
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index' },
        plugins: { legend: { labels: { color: '#8B8B9E', font: { size: 11 } } } },
        scales: {
            x: { ticks: { color: '#8B8B9E' }, grid: { color: 'rgba(42,42,56,0.8)' } },
            y: { ticks: { color: '#8B8B9E' }, grid: { color: 'rgba(42,42,56,0.8)' }, position: 'left' },
            y1: { ticks: { color: '#2DD4A0' }, grid: { display: false }, position: 'right' },
        }
    }
});
</script>
@endsection
