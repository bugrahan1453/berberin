@extends('layouts.panel')
@section('title', 'Hizmetler')
@section('page-title', 'Hizmet & Fiyatlar')
@section('page-subtitle', 'Sunduğunuz hizmetleri yönetin')

@section('page-actions')
<button onclick="document.getElementById('addServiceModal').style.display='flex'" class="btn-primary">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Hizmet Ekle
</button>
@endsection

@section('content')
<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
        <tr style="background:var(--color-surface-2);">
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">HİZMET ADI</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">KATEGORİ</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">SÜRE</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">FİYAT</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">CİNSİYET</th>
            <th style="padding:0.75rem 1rem; text-align:left; font-size:0.75rem; color:var(--color-muted); font-weight:600;">DURUM</th>
            <th style="padding:0.75rem 1rem;"></th>
        </tr>
        </thead>
        <tbody>
        @forelse($services as $svc)
        <tr style="border-top:1px solid var(--color-border); opacity:{{ $svc->is_active ? 1 : 0.5 }};">
            <td style="padding:0.75rem 1rem; font-size:0.875rem; font-weight:600;">{{ $svc->name }}</td>
            <td style="padding:0.75rem 1rem; font-size:0.8rem; color:var(--color-muted);">{{ $svc->category ?? '—' }}</td>
            <td style="padding:0.75rem 1rem; font-size:0.85rem;">{{ $svc->duration_min }} dk</td>
            <td style="padding:0.75rem 1rem; font-size:0.9rem; font-weight:700; color:var(--color-orange);">{{ number_format($svc->price,0,',','.') }}₺</td>
            <td style="padding:0.75rem 1rem; font-size:0.8rem; color:var(--color-muted);">{{ ['male'=>'Erkek','female'=>'Kadın','both'=>'Hepsi'][$svc->gender] }}</td>
            <td style="padding:0.75rem 1rem;">
                <form method="POST" action="{{ route('panel.services.toggle', $svc->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" style="padding:2px 10px;border-radius:6px;font-size:0.75rem;border:none;cursor:pointer;
                        background:{{ $svc->is_active ? 'rgba(45,212,160,0.15)' : 'rgba(139,139,158,0.15)' }};
                        color:{{ $svc->is_active ? '#2DD4A0' : '#8B8B9E' }};">
                        {{ $svc->is_active ? 'Aktif' : 'Pasif' }}
                    </button>
                </form>
            </td>
            <td style="padding:0.75rem 1rem;">
                <button onclick="editService({{ $svc->toJson() }})" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:0.8rem;">Düzenle</button>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center; padding:3rem; color:var(--color-muted);">Henüz hizmet eklenmedi.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<!-- Hizmet Ekle/Düzenle Modal -->
<div id="serviceModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:440px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 id="modalTitle" style="font-size:1rem; font-weight:700;">Hizmet Ekle</h3>
            <button onclick="closeModal()" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form id="serviceForm" method="POST" action="{{ route('panel.services.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Hizmet Adı *</label>
                <input type="text" name="name" id="svcName" class="input" required>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:0.75rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Fiyat (₺) *</label>
                    <input type="number" name="price" id="svcPrice" class="input" min="0" step="0.01" required>
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Süre (dk) *</label>
                    <input type="number" name="duration_min" id="svcDuration" class="input" min="5" value="30" required>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; margin-bottom:1rem;">
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Kategori</label>
                    <input type="text" name="category" id="svcCategory" class="input" placeholder="Saç, Sakal...">
                </div>
                <div>
                    <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Cinsiyet</label>
                    <select name="gender" id="svcGender" class="input">
                        <option value="both">Hepsi</option>
                        <option value="male">Erkek</option>
                        <option value="female">Kadın</option>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn-secondary">İptal</button>
                <button type="submit" class="btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
function closeModal() { document.getElementById('serviceModal').style.display = 'none'; }
document.getElementById('addServiceModal') && document.getElementById('addServiceModal').addEventListener('click', () => {
    document.getElementById('serviceModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Hizmet Ekle';
    document.getElementById('serviceForm').action = '{{ route('panel.services.store') }}';
    document.getElementById('formMethod').value = 'POST';
});
document.querySelector('[onclick*="addServiceModal"]')?.addEventListener('click', () => {
    document.getElementById('serviceModal').style.display = 'flex';
});
// Düzenle butonları
function editService(svc) {
    document.getElementById('serviceModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Hizmet Düzenle';
    document.getElementById('serviceForm').action = '/panel/services/' + svc.id;
    document.getElementById('formMethod').value = 'PATCH';
    document.getElementById('svcName').value = svc.name;
    document.getElementById('svcPrice').value = svc.price;
    document.getElementById('svcDuration').value = svc.duration_min;
    document.getElementById('svcCategory').value = svc.category || '';
    document.getElementById('svcGender').value = svc.gender;
}
// Ekle butonu
document.querySelector('[onclick*="addServiceModal"]').addEventListener('click', () => {
    document.getElementById('serviceModal').style.display = 'flex';
    document.getElementById('modalTitle').textContent = 'Hizmet Ekle';
    document.getElementById('serviceForm').action = '{{ route('panel.services.store') }}';
    document.getElementById('formMethod').value = 'POST';
    ['svcName','svcPrice','svcCategory'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('svcDuration').value = 30;
    document.getElementById('svcGender').value = 'both';
});
</script>
@endsection
