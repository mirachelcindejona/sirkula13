@if (is_array($data) && $data !== [])
    <ul class="small" style="margin:0;padding-left:16px">
        @foreach ($data as $key => $value)
            <li><strong>{{ $key }}:</strong> {{ is_scalar($value) || $value === null ? ($value === true ? 'ya' : ($value === false ? 'tidak' : ($value ?? '—'))) : json_encode($value) }}</li>
        @endforeach
    </ul>
@else
    <span class="small muted">—</span>
@endif
