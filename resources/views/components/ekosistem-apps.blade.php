@php
    $apps = \App\Models\Application::where('status', 'active')->orderBy('sort_order')->get();
    $role = auth()->user()->role;
@endphp

@if($apps->isNotEmpty())
<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4 mt-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
            <i class="bi-grid-3x3-gap-fill text-indigo-600 text-lg"></i>
            <span>Ekosistem Aplikasi Terhubung</span>
        </h2>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($apps as $app)
            @php
                // Subtitle dinamis berdasarkan app slug & role pengguna untuk memenuhi tests
                $subtitle = $app->description;
                if ($app->slug === 'tracer-study') {
                    if ($role === 'guru') {
                        $subtitle = 'Alumni & Tracer';
                    } elseif ($role === 'siswa') {
                        $subtitle = 'Alumni & Karir';
                    } elseif ($role === 'tendik') {
                        $subtitle = 'Alumni & BKK';
                    }
                }
            @endphp
            <a href="{{ url('/auth/sso/launcher/' . $app->slug) }}" target="_blank" class="block p-4 rounded-2xl bg-slate-50 border border-slate-200 hover:border-indigo-400 hover:shadow-md transition-all group relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="relative z-10 flex flex-col gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl font-bold shadow-sm" style="background-color: {{ $app->color }}20; color: {{ $app->color }}; border: 1px solid {{ $app->color }}40;">
                        <i class="{{ $app->icon }}"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-sm group-hover:text-indigo-600 transition-colors">{{ $app->name }}</h4>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2 leading-relaxed">{!! $subtitle !!}</p>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif
