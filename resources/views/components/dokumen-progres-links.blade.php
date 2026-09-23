{{-- Dokumen Progres Links --}}
@if($p->progresPelanggaran)
    @php $prog = $p->progresPelanggaran; @endphp
    <div class="flex flex-wrap gap-1">
        @if($prog->dokumen_laporan_bk)
            <a href="{{ asset('storage/' . $prog->dokumen_laporan_bk) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-indigo-700 bg-indigo-50 rounded hover:bg-indigo-100 transition-colors" title="Dokumen BK">
                📎 BK
            </a>
        @endif
        @if($prog->dokumen_laporan_walikelas)
            <a href="{{ asset('storage/' . $prog->dokumen_laporan_walikelas) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-50 rounded hover:bg-blue-100 transition-colors" title="Dokumen Wali Kelas">
                📎 Walikelas
            </a>
        @endif
        @if($prog->dokumen_laporan_kaprog)
            <a href="{{ asset('storage/' . $prog->dokumen_laporan_kaprog) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded hover:bg-emerald-100 transition-colors" title="Dokumen Kaprog">
                📎 Kaprog
            </a>
        @endif
        @if($prog->dokumen_laporan_waka)
            <a href="{{ asset('storage/' . $prog->dokumen_laporan_waka) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-purple-700 bg-purple-50 rounded hover:bg-purple-100 transition-colors" title="Dokumen Waka">
                📎 Waka
            </a>
        @endif
        @if($prog->dokumen_laporan_kepsek)
            <a href="{{ asset('storage/' . $prog->dokumen_laporan_kepsek) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-rose-700 bg-rose-50 rounded hover:bg-rose-100 transition-colors" title="Dokumen Kepala Sekolah">
                📎 Kepsek
            </a>
        @endif
        @if($prog->dokumen_walikelas)
            <a href="{{ asset('storage/' . $prog->dokumen_walikelas) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-cyan-700 bg-cyan-50 rounded hover:bg-cyan-100 transition-colors" title="Dokumen Approval Wali Kelas">
                📎 Apv.WK
            </a>
        @endif
        @if($prog->dokumen_kaprog)
            <a href="{{ asset('storage/' . $prog->dokumen_kaprog) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium text-teal-700 bg-teal-50 rounded hover:bg-teal-100 transition-colors" title="Dokumen Approval Kaprog">
                📎 Apv.KP
            </a>
        @endif
        @if(!$prog->dokumen_laporan_bk && !$prog->dokumen_laporan_walikelas && !$prog->dokumen_laporan_kaprog && !$prog->dokumen_laporan_waka && !$prog->dokumen_laporan_kepsek && !$prog->dokumen_walikelas && !$prog->dokumen_kaprog)
            <span class="text-gray-400 text-xs">-</span>
        @endif
    </div>
@else
    <span class="text-gray-400 text-xs">-</span>
@endif
