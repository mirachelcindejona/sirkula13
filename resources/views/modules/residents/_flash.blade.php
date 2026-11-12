@if (session('success'))
    <div role="status" class="mb-5 rounded-xl border border-primary-200 bg-primary-100 p-4 text-primary-800">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
        <strong>Mohon periksa data:</strong>
        <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
