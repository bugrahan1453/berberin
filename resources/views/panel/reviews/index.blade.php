@extends('layouts.panel')
@section('title', 'Yorumlar')
@section('page-title', 'Yorum Yönetimi')
@section('page-subtitle', 'Müşteri yorumlarını görüntüleyin ve yanıtlayın')

@section('content')
<div style="display:flex; flex-direction:column; gap:1rem;">
@forelse($reviews as $review)
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
        <div style="display:flex; align-items:center; gap:0.75rem;">
            <div style="width:38px;height:38px;border-radius:50%;background:var(--color-surface-3);display:flex;align-items:center;justify-content:center;font-weight:700;">
                {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
            </div>
            <div>
                <div style="font-size:0.875rem; font-weight:600;">{{ $review->user?->name ?? 'Anonim' }}</div>
                <div style="font-size:0.7rem; color:var(--color-muted);">{{ $review->created_at?->format('d.m.Y') }}</div>
            </div>
        </div>
        <div style="display:flex; gap:2px;">
            @for($i=1; $i<=5; $i++)
            <span style="color:{{ $i<=$review->rating ? '#F5C842' : '#3A3A4A' }}; font-size:0.9rem;">★</span>
            @endfor
        </div>
    </div>

    @if($review->comment)
    <p style="font-size:0.875rem; color:var(--color-muted); margin-bottom:0.75rem; line-height:1.5;">{{ $review->comment }}</p>
    @endif

    @if($review->reply)
    <div style="background:var(--color-surface-2); border-left:3px solid var(--color-orange); border-radius:0 8px 8px 0; padding:0.6rem 0.85rem; margin-bottom:0.75rem;">
        <div style="font-size:0.7rem; color:var(--color-orange); font-weight:600; margin-bottom:0.2rem;">Yanıtınız</div>
        <p style="font-size:0.85rem; color:var(--color-muted);">{{ $review->reply }}</p>
    </div>
    @endif

    @if(!$review->reply)
    <form method="POST" action="{{ route('panel.reviews.reply', $review->id) }}" style="display:flex; gap:0.5rem;">
        @csrf
        <input type="text" name="reply" class="input" placeholder="Yanıt yazın..." style="flex:1;" required>
        <button type="submit" class="btn-primary" style="white-space:nowrap;">Yanıtla</button>
    </form>
    @endif
</div>
@empty
<div style="text-align:center; padding:4rem; color:var(--color-muted);">
    <div style="font-size:2rem; margin-bottom:0.5rem;">⭐</div>
    Henüz yorum bulunmuyor.
</div>
@endforelse
</div>
{{ $reviews->links() }}
@endsection
