@if (session('success'))
    <div class="callout success">{{ session('success') }}</div>
@endif
@if (session('warning'))
    <div class="callout warning">{{ session('warning') }}</div>
@endif
@if (session('error'))
    <div class="callout warning">{{ session('error') }}</div>
@endif
