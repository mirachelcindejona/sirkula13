@extends('layouts.app')
@section('content')
<main class="mx-auto max-w-5xl px-4 py-8 text-ink">
    <p class="mb-1 text-xs font-bold uppercase tracking-widest text-primary-700">Superadmin · Administrasi RW 13</p>
    <h1 class="font-display text-4xl font-bold text-primary-900">Kesiapan operasional</h1>
    <p class="mt-2 mb-6 text-muted">Tandai keputusan yang sudah disepakati dan simpan rujukan rapat atau SOP. Perubahan dicatat dalam log audit.</p>
    @include('modules.residents._nav')
    @include('modules.residents._flash')
    <section class="mb-6 rounded-2xl border border-line bg-white p-6"><h2 class="text-sm font-bold text-primary-800">Keputusan selesai</h2><p class="font-display text-4xl font-bold text-primary-900">{{ $completed }} / {{ $items->count() }}</p><div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-primary-100"><div class="h-full rounded-full bg-primary-500" style="width: {{ $items->count() ? ($completed / $items->count()) * 100 : 0 }}%"></div></div><p class="mt-2 text-sm text-muted">{{ $items->count() - $completed }} keputusan masih memerlukan tindak lanjut.</p></section>
    <form action="{{ route('readiness.update') }}" method="POST" class="space-y-4">@csrf @method('PUT')
        @foreach($items as $item)
            @php $isCompleted = (bool) old('checks.'.$item->id.'.completed', $item->check?->is_completed ?? false); @endphp
            <section class="rounded-2xl border border-line bg-white p-5">
                <div class="flex items-start gap-4"><div class="pt-1"><input type="hidden" name="checks[{{ $item->id }}][completed]" value="0"><input type="checkbox" id="done_{{ $item->id }}" name="checks[{{ $item->id }}][completed]" value="1" @checked($isCompleted) class="h-6 w-6 accent-primary-500"></div><div class="flex-1"><label for="done_{{ $item->id }}" class="block font-bold text-primary-900">{{ $item->code }} · {{ $item->title }}</label><p class="mt-1 text-sm text-muted">{{ $item->description }}</p><label for="note_{{ $item->id }}" class="mt-3 block text-sm font-semibold">Rujukan rapat / SOP / tanggal keputusan</label><textarea id="note_{{ $item->id }}" name="checks[{{ $item->id }}][note]" rows="2" maxlength="2000" class="mt-1 w-full rounded-xl border border-primary-200 p-3" placeholder="Contoh: Notulen RW 13 tanggal 12/11/2026">{{ old('checks.'.$item->id.'.note', $item->check?->note) }}</textarea><span class="text-xs text-muted">Wajib diisi sebelum item ditandai selesai.</span>@if($item->check?->completed_at)<p class="mt-2 text-xs text-muted">Terakhir disahkan: {{ $item->check->completed_at->format('d/m/Y H:i') }} · {{ $item->check->completedBy?->name ?? 'Pengurus' }}</p>@endif</div></div>
            </section>
        @endforeach
        <button type="submit" class="rounded-xl bg-primary-500 px-6 py-3 font-bold text-white hover:bg-primary-700">Simpan checklist</button>
    </form>
</main>
@endsection
