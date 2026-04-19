@extends('layouts.panel')
@section('title', 'Koltuklar')
@section('page-title', 'Koltuk Yönetimi')
@section('page-subtitle', 'Anlık koltuk durumu ve yönetimi')

@section('page-actions')
<button onclick="document.getElementById('addSeatModal').style.display='flex'" class="btn-primary">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Koltuk Ekle
</button>
@endsection

@section('content')
<!-- Doluluk toggle -->
<div class="card-sm" style="margin-bottom:1.25rem; display:flex; align-items:center; justify-content:space-between;">
    <div>
        <div style="font-size:0.9rem; font-weight:600;">Dükkan Durumu</div>
        <div style="font-size:0.75rem; color:var(--color-muted); margin-top:2px;">
            {{ $shop->is_full ? '🔴 Şu an dolu görünüyorsunuz' : '🟢 Randevu kabul ediyorsunuz' }}
        </div>
    </div>
    <form method="POST" action="{{ route('panel.seats.toggle') }}">
        @csrf
        <button type="submit" style="padding:0.5rem 1.25rem; border-radius:10px; font-size:0.85rem; font-weight:600; cursor:pointer; border:none;
            background:{{ $shop->is_full ? 'rgba(45,212,160,0.15)' : 'rgba(239,68,68,0.15)' }};
            color:{{ $shop->is_full ? '#2DD4A0' : '#EF4444' }};">
            {{ $shop->is_full ? '✓ Müsait Yap' : '✕ Doluyuz' }}
        </button>
    </form>
</div>

<!-- Koltuk grid -->
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem;">
    @forelse($seats as $seat)
    <div class="card" style="border-left:4px solid {{ $seat->status==='empty'?'#2DD4A0':($seat->status==='busy'?'#EF4444':($seat->status==='reserved'?'#F5C842':'#8B8B9E')) }};">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
            <div>
                <div style="font-size:1rem; font-weight:700;">{{ $seat->name }}</div>
                @if($seat->is_vip)
                <span style="font-size:0.65rem; color:#F5C842; background:rgba(245,200,66,0.1); padding:1px 6px; border-radius:4px;">VIP</span>
                @endif
            </div>
            <span class="badge-{{ $seat->status }} badge-status">
                {{ ['empty'=>'Boş','busy'=>'Dolu','reserved'=>'Rezerve','inactive'=>'Pasif'][$seat->status] }}
            </span>
        </div>

        @if($seat->assignedStaff)
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.75rem;">
            👤 {{ $seat->assignedStaff->name }}
        </div>
        @endif

        @if($seat->status === 'busy' && $seat->currentAppointment)
        <div style="font-size:0.75rem; color:var(--color-muted); margin-bottom:0.75rem;">
            🧑 {{ $seat->currentAppointment->customer_name ?? $seat->currentAppointment->user?->name ?? 'Müşteri' }}
        </div>
        @endif

        <!-- Durum butonları -->
        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            @if($seat->status !== 'empty')
            <form method="POST" action="{{ route('panel.seats.status', $seat->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="empty">
                <button type="submit" style="padding:0.3rem 0.7rem;background:rgba(45,212,160,0.15);color:#2DD4A0;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Boşalt</button>
            </form>
            @endif
            @if($seat->status !== 'busy')
            <form method="POST" action="{{ route('panel.seats.status', $seat->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="busy">
                <button type="submit" style="padding:0.3rem 0.7rem;background:rgba(239,68,68,0.15);color:#EF4444;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Dolu</button>
            </form>
            @endif
            @if($seat->status !== 'inactive')
            <form method="POST" action="{{ route('panel.seats.status', $seat->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="inactive">
                <button type="submit" style="padding:0.3rem 0.7rem;background:rgba(139,139,158,0.15);color:#8B8B9E;border:none;border-radius:6px;font-size:0.75rem;cursor:pointer;">Pasif</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--color-muted);">
        Henüz koltuk eklenmedi. Koltuk Ekle butonuna tıklayın.
    </div>
    @endforelse
</div>

<!-- Koltuk Ekle Modal -->
<div id="addSeatModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:400px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1rem; font-weight:700;">Koltuk Ekle</h3>
            <button onclick="document.getElementById('addSeatModal').style.display='none'" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form method="POST" action="{{ route('panel.seats.store') }}">
            @csrf
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Koltuk Adı</label>
                <input type="text" name="name" class="input" placeholder="Koltuk 1 / VIP Koltuk" required>
            </div>
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Atanacak Personel</label>
                <select name="assigned_staff_id" class="input">
                    <option value="">Personel Atama</option>
                    @foreach($staff as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="is_vip" value="1" id="is_vip" style="accent-color:var(--color-orange);">
                <label for="is_vip" style="font-size:0.85rem;">VIP Koltuk</label>
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('addSeatModal').style.display='none'" class="btn-secondary">İptal</button>
                <button type="submit" class="btn-primary">Ekle</button>
            </div>
        </form>
    </div>
</div>
@endsection
