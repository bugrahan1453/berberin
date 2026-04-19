@extends('layouts.panel')
@section('title', 'Randevular')
@section('page-title', 'Randevular')
@section('page-subtitle', 'Günlük randevu yönetimi')

@section('page-actions')
<input type="date" value="{{ $date }}" onchange="window.location='?date='+this.value" class="input" style="width:160px;">
<button onclick="document.getElementById('newApptModal').style.display='flex'" class="btn-primary">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Randevu Ekle
</button>
@endsection

@section('content')
<!-- Filtre tabları -->
<div style="display:flex; gap:0.5rem; margin-bottom:1.25rem;">
    @foreach([''=>'Tümü','pending'=>'Bekleyen','confirmed'=>'Onaylı','in_progress'=>'İşlemde','completed'=>'Tamamlanan','cancelled'=>'İptal'] as $val=>$label)
    <a href="?date={{ $date }}&status={{ $val }}" style="padding:0.35rem 0.9rem; border-radius:8px; font-size:0.8rem; font-weight:500; text-decoration:none;
        background:{{ $status==$val ? 'var(--color-orange)' : 'var(--color-surface-2)' }};
        color:{{ $status==$val ? 'white' : 'var(--color-muted)' }};
        border:1px solid {{ $status==$val ? 'transparent' : 'var(--color-border)' }};">{{ $label }}</a>
    @endforeach
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
        <tr style="background:var(--color-surface-2);">
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">SAAT</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">MÜŞTERİ</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">HİZMET</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">PERSONEL</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">FİYAT</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">DURUM</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">İŞLEM</th>
        </tr>
        </thead>
        <tbody>
        @forelse($appointments as $appt)
        <tr style="border-top:1px solid var(--color-border);" x-data="{}">
            <td style="padding:0.75rem 1rem; font-size:0.875rem; font-weight:600; color:var(--color-orange);">
                {{ $appt->time }}<span style="color:var(--color-muted); font-weight:400;"> – {{ $appt->end_time }}</span>
            </td>
            <td style="padding:0.75rem 1rem;">
                <div style="font-size:0.875rem; font-weight:600;">{{ $appt->customer_name ?? $appt->user?->name ?? 'Walk-in' }}</div>
                <div style="font-size:0.75rem; color:var(--color-muted);">{{ $appt->customer_phone ?? $appt->user?->phone ?? '' }}</div>
            </td>
            <td style="padding:0.75rem 1rem; font-size:0.85rem;">{{ $appt->service?->name }}</td>
            <td style="padding:0.75rem 1rem; font-size:0.85rem; color:var(--color-muted);">{{ $appt->staff?->name ?? '—' }}</td>
            <td style="padding:0.75rem 1rem; font-size:0.85rem; font-weight:600;">{{ number_format($appt->price,0,',','.') }}₺</td>
            <td style="padding:0.75rem 1rem;">
                <span class="badge-{{ $appt->status }} badge-status">
                    {{ ['pending'=>'Bekliyor','confirmed'=>'Onaylı','in_progress'=>'İşlemde','completed'=>'Bitti','cancelled'=>'İptal','no_show'=>'Gelmedi'][$appt->status] }}
                </span>
            </td>
            <td style="padding:0.75rem 1rem;">
                <div style="display:flex; gap:0.4rem;">
                    @if($appt->status === 'pending')
                    <form method="POST" action="{{ route('panel.appointments') }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
                        <input type="hidden" name="new_status" value="confirmed">
                        <button type="submit" style="padding:0.3rem 0.6rem;background:rgba(45,212,160,0.15);color:#2DD4A0;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Onayla</button>
                    </form>
                    @endif
                    @if(in_array($appt->status, ['confirmed']))
                    <form method="POST" action="{{ route('panel.appointments') }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
                        <input type="hidden" name="new_status" value="in_progress">
                        <button type="submit" style="padding:0.3rem 0.6rem;background:rgba(91,141,239,0.15);color:#5B8DEF;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Başlat</button>
                    </form>
                    @endif
                    @if(in_array($appt->status, ['in_progress']))
                    <form method="POST" action="{{ route('panel.appointments') }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
                        <input type="hidden" name="new_status" value="completed">
                        <button type="submit" style="padding:0.3rem 0.6rem;background:rgba(45,212,160,0.15);color:#2DD4A0;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Bitti</button>
                    </form>
                    @endif
                    @if(!in_array($appt->status, ['completed','cancelled','no_show']))
                    <form method="POST" action="{{ route('panel.appointments') }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
                        <input type="hidden" name="new_status" value="cancelled">
                        <button type="submit" style="padding:0.3rem 0.6rem;background:rgba(239,68,68,0.1);color:#EF4444;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">İptal</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center; padding:3rem; color:var(--color-muted); font-size:0.875rem;">Bu tarih için randevu bulunamadı.</td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

<!-- Yeni Randevu Modal -->
<div id="newApptModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:480px; max-height:90vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1rem; font-weight:700;">Yeni Randevu</h3>
            <button onclick="document.getElementById('newApptModal').style.display='none'" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form method="POST" action="{{ route('panel.appointments') }}">
            @csrf
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Müşteri Adı</label>
                    <input type="text" name="customer_name" class="input" placeholder="Ad Soyad">
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Telefon</label>
                    <input type="tel" name="customer_phone" class="input" placeholder="05xx...">
                </div>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Hizmet</label>
                <select name="service_id" class="input" required>
                    <option value="">Seçin...</option>
                    @foreach($services as $svc)
                    <option value="{{ $svc->id }}">{{ $svc->name }} — {{ $svc->price }}₺ ({{ $svc->duration_min }}dk)</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Personel</label>
                    <select name="staff_id" class="input">
                        <option value="">Fark etmez</option>
                        @foreach($staff as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Koltuk</label>
                    <select name="seat_id" class="input">
                        <option value="">Otomatik</option>
                        @foreach($seats as $seat)
                        <option value="{{ $seat->id }}">{{ $seat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Tarih</label>
                    <input type="date" name="date" value="{{ $date }}" class="input" required>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Saat</label>
                    <input type="time" name="time" class="input" required>
                </div>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Kaynak</label>
                <select name="source" class="input">
                    <option value="walkin">Walk-in</option>
                    <option value="phone">Telefon</option>
                    <option value="app">App</option>
                </select>
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('newApptModal').style.display='none'" class="btn-secondary">İptal</button>
                <button type="submit" class="btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection
