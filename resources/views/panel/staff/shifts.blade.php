@extends('layouts.panel')
@section('title', 'Vardiyalar')
@section('page-title', $staffMember->name . ' — Vardiyalar')
@section('page-subtitle', $weekStart->locale('tr')->isoFormat('D MMMM') . ' – ' . $weekStart->copy()->addDays(6)->locale('tr')->isoFormat('D MMMM YYYY'))

@section('page-actions')
<a href="{{ route('panel.staff.shifts', [$staffMember->id, 'week_start' => $weekStart->copy()->subWeek()->toDateString()]) }}" class="btn-secondary">← Önceki</a>
<a href="{{ route('panel.staff.shifts', [$staffMember->id, 'week_start' => $weekStart->copy()->addWeek()->toDateString()]) }}" class="btn-secondary">Sonraki →</a>
@endsection

@section('content')
<div class="card">
    <form method="POST" action="{{ route('panel.staff.shifts.save', $staffMember->id) }}">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(7,1fr); gap:0.5rem;">
        @php $days = ['Pzt','Sal','Çar','Per','Cum','Cmt','Paz']; @endphp
        @for($i = 0; $i < 7; $i++)
            @php $date = $weekStart->copy()->addDays($i)->toDateString(); $shift = $shifts[$date] ?? null; @endphp
            <div style="background:var(--color-surface-2); border-radius:10px; padding:0.75rem;">
                <div style="font-size:0.75rem; font-weight:600; color:var(--color-orange); margin-bottom:0.5rem;">{{ $days[$i] }}</div>
                <div style="font-size:0.7rem; color:var(--color-muted); margin-bottom:0.75rem;">{{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>

                <div style="margin-bottom:0.4rem;">
                    <label style="font-size:0.65rem; color:var(--color-muted);">Başlangıç</label>
                    <input type="time" name="shifts[{{ $date }}][start_time]" value="{{ $shift?->start_time ?? '09:00' }}" class="input" style="font-size:0.75rem;padding:0.3rem;">
                </div>
                <div style="margin-bottom:0.4rem;">
                    <label style="font-size:0.65rem; color:var(--color-muted);">Bitiş</label>
                    <input type="time" name="shifts[{{ $date }}][end_time]" value="{{ $shift?->end_time ?? '18:00' }}" class="input" style="font-size:0.75rem;padding:0.3rem;">
                </div>
                <div style="margin-top:0.5rem; display:flex; align-items:center; gap:0.3rem;">
                    <input type="checkbox" name="shifts[{{ $date }}][is_off]" value="1" {{ $shift?->is_off ? 'checked' : '' }} style="accent-color:var(--color-orange);">
                    <label style="font-size:0.7rem; color:var(--color-muted);">İzin</label>
                </div>
            </div>
        @endfor
        </div>
        <div style="text-align:right; margin-top:1rem;">
            <button type="submit" class="btn-primary">Vardiyaları Kaydet</button>
        </div>
    </form>
</div>
@endsection
