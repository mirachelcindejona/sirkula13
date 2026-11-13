@php($event = $event ?? null)
<div class="form-grid">
    <div class="field full">
        <label for="event-title">Nama kegiatan <span class="required">*</span></label>
        <input id="event-title" name="title" required maxlength="180" value="{{ old('title', $event?->title) }}">
    </div>
    <div class="field">
        <label for="event-category">Kategori</label>
        <input id="event-category" name="category" maxlength="80" value="{{ old('category', $event?->category) }}" placeholder="Kerja bakti, pelatihan">
    </div>
    <div class="field">
        <label for="event-points">Poin kehadiran <span class="required">*</span></label>
        <input id="event-points" name="attendance_points" type="number" min="0" max="500" step="2" required value="{{ old('attendance_points', $event?->attendance_points ?? 50) }}">
        <div class="field-hint">Poin harus bilangan genap, tanpa koma.</div>
    </div>
    <div class="field">
        <label for="event-date">Tanggal <span class="required">*</span></label>
        <input id="event-date" name="event_date" type="date" required value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}">
    </div>
    <div class="field">
        <label for="event-time">Waktu</label>
        <input id="event-time" name="event_time" value="{{ old('event_time', $event?->event_time) }}" placeholder="08.00–10.00">
    </div>
    <div class="field">
        <label for="event-location">Lokasi <span class="required">*</span></label>
        <input id="event-location" name="location" required value="{{ old('location', $event?->location) }}">
    </div>
    <div class="field">
        <label for="event-quota">Kuota</label>
        <input id="event-quota" name="quota" type="number" min="1" value="{{ old('quota', $event?->quota) }}">
    </div>
    <div class="field full">
        <label for="event-description">Keterangan</label>
        <textarea id="event-description" name="description">{{ old('description', $event?->description) }}</textarea>
    </div>
    <x-photo-field id="event-poster" name="poster" label="Poster kegiatan" :current="$event?->poster_path ? \App\Support\Uploads::url($event->poster_path) : null" />
</div>
