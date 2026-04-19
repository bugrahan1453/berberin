@extends('layouts.panel')
@section('title', 'Ayarlar')
@section('page-title', 'Dükkan Ayarları')
@section('page-subtitle', 'Genel bilgiler, çalışma saatleri ve sistem ayarları')

@section('content')
<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">

<!-- Sol: Dükkan Bilgileri -->
<div>
    <div class="card" style="margin-bottom:1rem;">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; padding-bottom:0.6rem; border-bottom:1px solid var(--color-border);">Dükkan Bilgileri</h3>
        <form method="POST" action="{{ route('panel.settings.update') }}">
            @csrf
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Dükkan Adı</label>
                <input type="text" name="name" value="{{ $shop->name }}" class="input" required>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Adres</label>
                <textarea name="address" class="input" rows="2">{{ $shop->address }}</textarea>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Şehir</label>
                    <input type="text" name="city" value="{{ $shop->city }}" class="input">
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">İlçe</label>
                    <input type="text" name="district" value="{{ $shop->district }}" class="input">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Telefon</label>
                    <input type="tel" name="phone" value="{{ $shop->phone }}" class="input">
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Cinsiyet Filtresi</label>
                    <select name="gender_filter" class="input">
                        <option value="both" {{ $shop->gender_filter==='both'?'selected':'' }}>Hepsi</option>
                        <option value="male" {{ $shop->gender_filter==='male'?'selected':'' }}>Sadece Erkek</option>
                        <option value="female" {{ $shop->gender_filter==='female'?'selected':'' }}>Sadece Kadın</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Instagram</label>
                <input type="text" name="instagram" value="{{ $shop->instagram }}" class="input" placeholder="@hesap">
            </div>

            <h4 style="font-size:0.85rem; font-weight:600; margin:1rem 0 0.75rem; color:var(--color-muted);">Randevu Ayarları</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Slot Aralığı (dk)</label>
                    <select name="slot_interval" class="input">
                        @foreach([15,30,45,60] as $min)
                        <option value="{{ $min }}" {{ $settings->slot_interval==$min?'selected':'' }}>{{ $min }} dk</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Min Önceden Rezerv (sa)</label>
                    <input type="number" name="min_advance_hours" value="{{ $settings->min_advance_hours }}" class="input" min="0">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Max Gün (ileriye)</label>
                    <input type="number" name="max_advance_days" value="{{ $settings->max_advance_days }}" class="input" min="1" max="90">
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">İptal Süresi (sa)</label>
                    <input type="number" name="cancel_hours" value="{{ $settings->cancel_hours }}" class="input" min="0">
                </div>
            </div>
            <div style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom:1rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; cursor:pointer;">
                    <input type="checkbox" name="auto_approve" value="1" {{ $settings->auto_approve?'checked':'' }} style="accent-color:var(--color-orange);">
                    Otomatik onay
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; cursor:pointer;">
                    <input type="checkbox" name="walkin_enabled" value="1" {{ $settings->walkin_enabled?'checked':'' }} style="accent-color:var(--color-orange);">
                    Walk-in kabul
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.85rem; cursor:pointer;">
                    <input type="checkbox" name="deposit_required" value="1" {{ $settings->deposit_required?'checked':'' }} style="accent-color:var(--color-orange);">
                    Depozito zorunlu
                </label>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Ayarları Kaydet</button>
        </form>
    </div>
</div>

<!-- Sağ: Çalışma Saatleri + Bildirimler -->
<div>
    <div class="card" style="margin-bottom:1rem;">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; padding-bottom:0.6rem; border-bottom:1px solid var(--color-border);">Çalışma Saatleri</h3>
        <form method="POST" action="{{ route('panel.settings.update') }}">
            @csrf
            @php $days = ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar']; @endphp
            @foreach($days as $i => $day)
            @php $hour = $shop->hours->where('day_of_week', $i)->first(); @endphp
            <div style="display:grid; grid-template-columns:80px 1fr 1fr 50px; gap:0.5rem; align-items:center; margin-bottom:0.5rem;">
                <div style="font-size:0.8rem; font-weight:500;">{{ $day }}</div>
                <input type="time" name="hours[{{ $i }}][open_time]" value="{{ $hour?->open_time ?? '09:00' }}" class="input" style="font-size:0.8rem;padding:0.35rem;">
                <input type="time" name="hours[{{ $i }}][close_time]" value="{{ $hour?->close_time ?? '18:00' }}" class="input" style="font-size:0.8rem;padding:0.35rem;">
                <label style="display:flex; align-items:center; gap:0.3rem; font-size:0.7rem; cursor:pointer;">
                    <input type="checkbox" name="hours[{{ $i }}][is_closed]" value="1" {{ $hour?->is_closed ? 'checked' : '' }} style="accent-color:var(--color-red);">
                    Kapalı
                </label>
            </div>
            @endforeach
            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; margin-top:0.75rem;">Kaydet</button>
        </form>
    </div>

    <!-- Bildirim Ayarları -->
    <div class="card">
        <h3 style="font-size:0.95rem; font-weight:700; margin-bottom:1rem; padding-bottom:0.6rem; border-bottom:1px solid var(--color-border);">Bildirim Ayarları</h3>
        <form method="POST" action="{{ route('panel.settings.notifications') }}">
            @csrf
            <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1rem;">
                @php $notif = $notifSettings; @endphp
                <label style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>Sabah özeti</span>
                    <div style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="time" name="morning_summary_time" value="{{ $notif->morning_summary_time ?? '08:00' }}" class="input" style="width:90px;font-size:0.8rem;padding:0.35rem;">
                        <input type="checkbox" name="morning_summary_enabled" value="1" {{ $notif->morning_summary_enabled ? 'checked' : '' }} style="accent-color:var(--color-orange);">
                    </div>
                </label>
                <label style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>Akşam özeti</span>
                    <input type="checkbox" name="evening_summary_enabled" value="1" {{ $notif->evening_summary_enabled ? 'checked' : '' }} style="accent-color:var(--color-orange);">
                </label>
                <label style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>Haftalık rapor</span>
                    <input type="checkbox" name="weekly_report_enabled" value="1" {{ $notif->weekly_report_enabled ? 'checked' : '' }} style="accent-color:var(--color-orange);">
                </label>
                <label style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>SMS gönderimi</span>
                    <input type="checkbox" name="sms_enabled" value="1" {{ $notif->sms_enabled ? 'checked' : '' }} style="accent-color:var(--color-orange);">
                </label>
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>Hatırlatma (saat önce)</span>
                    <select name="reminder_hours" class="input" style="width:80px;">
                        @foreach([1,2,3,4] as $h)
                        <option value="{{ $h }}" {{ $notif->reminder_hours==$h ? 'selected' : '' }}>{{ $h }} sa</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:0.85rem;">
                    <span>No-show işaretleme (dk)</span>
                    <select name="no_show_auto_mark_minutes" class="input" style="width:80px;">
                        @foreach([15,20,30,45,60] as $m)
                        <option value="{{ $m }}" {{ $notif->no_show_auto_mark_minutes==$m ? 'selected' : '' }}>{{ $m }} dk</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary" style="width:100%; justify-content:center;">Bildirim Ayarlarını Kaydet</button>
        </form>
    </div>
</div>

</div>

<!-- QR Kod linki -->
<div class="card-sm" style="margin-top:1rem; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <div style="font-size:0.875rem; font-weight:600;">QR Kod</div>
        <div style="font-size:0.75rem; color:var(--color-muted);">Dükkan girişine asın</div>
    </div>
    <a href="{{ route('panel.qr') }}" class="btn-secondary">QR Kodu Görüntüle</a>
</div>
@endsection
