@extends('layouts.panel')
@section('title', 'Personel')
@section('page-title', 'Personel Yönetimi')
@section('page-subtitle', 'Çalışanlarınızı ve vardiyalarını yönetin')

@section('page-actions')
<button onclick="document.getElementById('addStaffModal').style.display='flex'" class="btn-primary">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Personel Ekle
</button>
@endsection

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:1rem;">
@forelse($staff as $member)
<div class="card" style="opacity:{{ $member->is_active ? 1 : 0.5 }};">
    <div style="display:flex; align-items:center; gap:0.85rem; margin-bottom:1rem;">
        <div style="width:44px;height:44px;border-radius:50%;background:var(--color-orange);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;flex-shrink:0;">
            {{ strtoupper(substr($member->name, 0, 1)) }}
        </div>
        <div>
            <div style="font-weight:700; font-size:0.95rem;">{{ $member->name }}</div>
            <div style="font-size:0.75rem; color:var(--color-muted);">{{ ['owner'=>'Sahip','manager'=>'Yönetici','staff'=>'Personel'][$member->role] }}</div>
        </div>
    </div>
    @if($member->specialties)
    <div style="display:flex; flex-wrap:wrap; gap:0.3rem; margin-bottom:0.75rem;">
        @foreach($member->specialties as $spec)
        <span style="font-size:0.7rem; background:rgba(255,107,53,0.12); color:var(--color-orange); padding:2px 8px; border-radius:20px;">{{ $spec }}</span>
        @endforeach
    </div>
    @endif
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
        <a href="{{ route('panel.staff.shifts', $member->id) }}" class="btn-secondary" style="font-size:0.75rem; padding:0.3rem 0.7rem;">Vardiyalar</a>
        <form method="POST" action="{{ route('panel.staff.toggle', $member->id) }}" style="display:inline;">
            @csrf @method('PATCH')
            <button type="submit" style="padding:0.3rem 0.7rem;font-size:0.75rem;border:1px solid var(--color-border);border-radius:6px;cursor:pointer;background:var(--color-surface-3);color:var(--color-muted);">
                {{ $member->is_active ? 'Pasif Yap' : 'Aktif Et' }}
            </button>
        </form>
    </div>
</div>
@empty
<div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--color-muted);">Henüz personel eklenmedi.</div>
@endforelse
</div>

<!-- Personel Ekle Modal -->
<div id="addStaffModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:440px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1rem; font-weight:700;">Personel Ekle</h3>
            <button onclick="document.getElementById('addStaffModal').style.display='none'" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form method="POST" action="{{ route('panel.staff.store') }}">
            @csrf
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Ad Soyad *</label>
                <input type="text" name="name" class="input" required>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Telefon</label>
                <input type="tel" name="phone" class="input" placeholder="05xx...">
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Rol</label>
                <select name="role" class="input">
                    <option value="staff">Personel</option>
                    <option value="manager">Yönetici</option>
                    <option value="owner">Sahip</option>
                </select>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Uzmanlıklar (virgülle ayırın)</label>
                <input type="text" name="specialties_raw" class="input" placeholder="saç, sakal, cilt bakım">
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Komisyon Oranı (%)</label>
                <input type="number" name="commission_rate" class="input" value="0" min="0" max="100">
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('addStaffModal').style.display='none'" class="btn-secondary">İptal</button>
                <button type="submit" class="btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection
