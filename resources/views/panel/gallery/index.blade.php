@extends('layouts.panel')
@section('title', 'Galeri')
@section('page-title', 'Galeri')
@section('page-subtitle', 'Dükkan fotoğraflarını yönetin')

@section('page-actions')
<button onclick="document.getElementById('addPhotoModal').style.display='flex'" class="btn-primary">Fotoğraf Ekle</button>
@endsection

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:1rem;">
@forelse($gallery as $item)
<div style="position:relative; border-radius:10px; overflow:hidden; aspect-ratio:1;">
    <img src="{{ $item->image_url }}" alt="{{ $item->caption }}" style="width:100%; height:100%; object-fit:cover;">
    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 50%); display:flex; align-items:flex-end; padding:0.6rem;">
        <div style="flex:1;">
            @if($item->caption)<p style="color:white;font-size:0.75rem;margin:0;">{{ $item->caption }}</p>@endif
        </div>
        <form method="POST" action="{{ route('panel.gallery.delete', $item->id) }}">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Silmek istediğinizden emin misiniz?')" style="background:rgba(239,68,68,0.8);border:none;border-radius:6px;color:white;padding:0.25rem 0.5rem;font-size:0.7rem;cursor:pointer;">✕</button>
        </form>
    </div>
</div>
@empty
<div style="grid-column:1/-1; text-align:center; padding:3rem; color:var(--color-muted);">
    <div style="font-size:2rem; margin-bottom:0.5rem;">📷</div>
    Henüz fotoğraf eklenmedi.
</div>
@endforelse
</div>

<!-- Fotoğraf Ekle Modal -->
<div id="addPhotoModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:100; align-items:center; justify-content:center;">
    <div class="card" style="width:100%; max-width:400px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1rem; font-weight:700;">Fotoğraf Ekle</h3>
            <button onclick="document.getElementById('addPhotoModal').style.display='none'" style="background:none;border:none;color:var(--color-muted);cursor:pointer;font-size:1.2rem;">✕</button>
        </div>
        <form method="POST" action="{{ route('panel.gallery.store') }}">
            @csrf
            <div style="margin-bottom:0.75rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Fotoğraf URL *</label>
                <input type="url" name="image_url" class="input" placeholder="https://..." required>
            </div>
            <div style="margin-bottom:1rem;">
                <label style="font-size:0.75rem; color:var(--color-muted); display:block; margin-bottom:0.3rem;">Açıklama</label>
                <input type="text" name="caption" class="input" placeholder="Fotoğraf açıklaması">
            </div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('addPhotoModal').style.display='none'" class="btn-secondary">İptal</button>
                <button type="submit" class="btn-primary">Ekle</button>
            </div>
        </form>
    </div>
</div>
@endsection
