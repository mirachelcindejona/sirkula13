@props(['navigation'])
<aside class="sidebar" id="sidebar" aria-label="Menu utama">
    <div class="row between" style="padding:7px 9px 12px">
        <div>
            <div class="small strong">Menu navigasi</div>
            <div class="tiny muted">{{ auth()->user()->primaryRoleName() }}</div>
        </div>
        <button class="icon-button" type="button" data-drawer-close aria-label="Tutup menu" style="width:34px;height:34px"><x-icon name="x" /></button>
    </div>
    <div class="sidebar-scroll">
        @foreach ($navigation['groups'] as $group)
            <x-app.nav-group :group="$group" />
        @endforeach
    </div>
</aside>
