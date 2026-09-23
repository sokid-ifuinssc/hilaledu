@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div class="text-slate-500 font-medium order-2 sm:order-1">
            Menampilkan <span class="font-bold text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span> sampai <span class="font-bold text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> hasil
        </div>

        <div class="inline-flex items-center gap-1.5 order-1 sm:order-2">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 font-bold cursor-not-allowed inline-flex items-center gap-1.5 select-none opacity-60">
                    <i class="bi-chevron-left text-xs"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold hover:text-blue-600 transition inline-flex items-center gap-1.5 shadow-xs">
                    <i class="bi-chevron-left text-xs"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Nomor Halaman --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    {{-- Separator Titik-titik (...) --}}
                    @if (is_string($element))
                        <span class="px-2 py-1 text-slate-400 font-bold tracking-wider select-none">{{ $element }}</span>
                    @endif

                    {{-- Array Link Halaman --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="min-w-[32px] h-8 px-2.5 rounded-xl bg-blue-600 text-white font-black flex items-center justify-center shadow-xs shadow-blue-500/20">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="min-w-[32px] h-8 px-2.5 rounded-xl bg-white border border-slate-200 hover:border-blue-300 text-slate-600 hover:text-blue-600 hover:bg-blue-50/50 font-bold flex items-center justify-center transition">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold hover:text-blue-600 transition inline-flex items-center gap-1.5 shadow-xs">
                    <span class="hidden sm:inline">Selanjutnya</span>
                    <i class="bi-chevron-right text-xs"></i>
                </a>
            @else
                <span class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 font-bold cursor-not-allowed inline-flex items-center gap-1.5 select-none opacity-60">
                    <span class="hidden sm:inline">Selanjutnya</span>
                    <i class="bi-chevron-right text-xs"></i>
                </span>
            @endif
        </div>
    </nav>
@else
    @if ($paginator->total() > 0)
        <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
            <span>Menampilkan seluruh <span class="font-bold text-slate-800">{{ $paginator->total() }}</span> data</span>
        </div>
    @endif
@endif
