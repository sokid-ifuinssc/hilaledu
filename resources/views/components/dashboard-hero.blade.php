@props([
    'icon' => 'bi-speedometer2',
    'badge' => 'Pusat Kendali',
    'title' => 'Dashboard',
    'description' => '',
])

<div class="p-5 sm:p-7 rounded-2xl bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-5 border border-slate-800 relative overflow-hidden mb-6">
    <!-- Background Foto Kampus Aksen Halus -->
    <div class="absolute inset-0 bg-cover bg-center opacity-10 mix-blend-luminosity pointer-events-none" style="background-image: url('{{ asset('images/gedung-sekolah-lapangan.jpg') }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-emerald-950/80 to-slate-900/90 pointer-events-none"></div>

    <div class="space-y-2 relative z-10 flex-1">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 font-bold text-xs border border-amber-400/30">
            <i class="{{ $icon }}"></i>
            <span>{{ $badge }}</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight">{{ $title }}</h1>
        @if($description)
        <p class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed">
            {{ $description }}
        </p>
        @endif
    </div>

    @if(isset($action))
    <div class="flex items-center gap-3 shrink-0 relative z-10">
        {{ $action }}
    </div>
    @endif
</div>
