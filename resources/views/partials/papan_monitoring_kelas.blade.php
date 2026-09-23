@php
    $todayDate = $todayDate ?? date('Y-m-d');
    $hariPantau = $hariPantau ?? \App\Models\JadwalPelajaran::getHariIndonesia();
    $jamPantau = $jamPantau ?? now()->format('H:i:s');
    $periodInfo = $periodInfo ?? \App\Services\MonitoringKelasService::getCurrentPeriodInfo($hariPantau, $jamPantau);
@endphp

<!-- Papan Monitoring Kehadiran Kelas Real-Time (Full-Width, Jelas, Kontras Tinggi, Tanpa Geser-Geser) -->
<div id="papan-monitoring-wrapper" class="bg-white rounded-3xl p-5 border border-slate-200 shadow-xs space-y-4 w-full transition-all">
    
    <!-- Top Header: Judul, Status Jam Ke-Berapa Real-Time, & Indikator Rekap -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
        
        <!-- Sisi Kiri: Judul & Jam/Tanggal Digital Real-Time -->
        <div class="space-y-1">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <h2 class="font-black text-slate-900 text-base sm:text-lg tracking-tight">
                    Monitoring Kehadiran Kelas Real-Time
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 border border-blue-200">
                    Live Transparan
                </span>
            </div>

            <!-- Tanggal & Waktu Live Digital -->
            <div class="flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                <span class="inline-flex items-center gap-1.5 font-bold text-slate-700">
                    <i class="bi-calendar3 text-indigo-600"></i>
                    <span id="live-monitoring-date">{{ $hariPantau }}, {{ \Carbon\Carbon::parse($todayDate)->isoFormat('D MMMM Y') }}</span>
                </span>
                <span class="text-slate-300">•</span>
                <span class="inline-flex items-center gap-1.5 font-mono font-black text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-lg text-xs tracking-tight shadow-2xs">
                    <i class="bi-clock-fill text-indigo-600 text-[11px]"></i>
                    <span id="live-monitoring-clock">{{ substr($jamPantau, 0, 5) }}:{{ substr($jamPantau, 6, 2) ?: '00' }} WIB</span>
                </span>
            </div>
        </div>

        <!-- Sisi Tengah: INFORMASI SAAT INI JAM KEBERAPA (Highlight Utama Sangat Jelas) -->
        <div class="flex items-center">
            <div class="inline-flex items-center gap-3 px-3.5 py-2 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 border-2 border-indigo-200 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-sm shadow-sm shrink-0">
                    <i class="bi-bell-fill"></i>
                </div>
                <div class="leading-tight">
                    <div class="text-[10px] uppercase font-black tracking-wider text-indigo-900/70">
                        Status KBM Saat Ini:
                    </div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span id="live-period-label" class="text-sm font-black text-slate-900 tracking-tight">
                            {{ $periodInfo['label_jam_ke'] ?? 'Jam ke-2' }}
                        </span>
                        <span id="live-period-badge" class="text-[11px] font-black px-2.5 py-0.5 rounded-full {{ $periodInfo['badge_class'] ?? 'bg-emerald-600 text-white shadow-2xs' }}">
                            {{ $periodInfo['rentang_waktu'] ?? '07:45 - 08:30 WIB' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sisi Kanan: Rekap Ringkas Status Kelas & Tombol Refresh -->
        <div class="flex items-center gap-1.5 flex-wrap text-xs font-bold">
            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center gap-1.5 text-xs font-extrabold" title="Kelas Ada Guru">
                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                <span>Ada Guru: <strong id="rekap-ada-guru">{{ $rekapMonitoring['ada_guru'] ?? 0 }}</strong></span>
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1.5 text-xs font-extrabold" title="Guru Izin">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Izin: <strong id="rekap-izin">{{ $rekapMonitoring['izin'] ?? 0 }}</strong></span>
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-rose-100 text-rose-900 border border-rose-300 flex items-center gap-1.5 text-xs font-extrabold" title="Guru Sakit atau Belum Hadir">
                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                <span>Blm Hadir: <strong id="rekap-sakit">{{ $rekapMonitoring['sakit'] ?? 0 }}</strong></span>
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-blue-100 text-blue-900 border border-blue-300 flex items-center gap-1.5 text-xs font-extrabold" title="Guru Tugas Luar Dinas">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>Tgs Luar: <strong id="rekap-tugas-luar">{{ $rekapMonitoring['tugas_luar'] ?? 0 }}</strong></span>
            </span>
            
            <!-- Tombol Refresh Ringan Real-Time -->
            <button type="button" onclick="refreshMonitoringData(this)" title="Perbarui Data Real-Time Sekarang" 
                    class="p-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 transition flex items-center justify-center shadow-2xs">
                <i class="bi-arrow-clockwise text-sm"></i>
            </button>
        </div>
    </div>

    <!-- GRID MONITORING KELAS: Informasi Jelas, Tulisan Kontras & Mudah Dibaca -->
    <div id="monitoring-grid-container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-9 gap-3 w-full">
        @foreach($monitoringKelas as $item)
        @php
            $warna = $item['warna'];
            $cardClasses = match($warna) {
                'hijau' => 'bg-emerald-50/90 border-2 border-emerald-500 text-emerald-950 hover:bg-emerald-100 shadow-xs hover:shadow-md',
                'kuning' => 'bg-amber-50/90 border-2 border-amber-400 text-amber-950 hover:bg-amber-100 shadow-xs hover:shadow-md',
                'merah' => 'bg-rose-50/90 border-2 border-rose-400 text-rose-950 hover:bg-rose-100 shadow-xs hover:shadow-md',
                'biru' => 'bg-blue-50/90 border-2 border-blue-400 text-blue-950 hover:bg-blue-100 shadow-xs hover:shadow-md',
                default => 'bg-slate-50/95 border-2 border-slate-300 text-slate-800 hover:bg-white shadow-2xs hover:shadow-xs',
            };
            $badgeWarna = match($warna) {
                'hijau' => 'bg-emerald-700 text-white',
                'kuning' => 'bg-amber-500 text-slate-950',
                'merah' => 'bg-rose-700 text-white',
                'biru' => 'bg-blue-700 text-white',
                default => 'bg-slate-600 text-white',
            };
            $dotWarna = match($warna) {
                'hijau' => 'bg-emerald-600',
                'kuning' => 'bg-amber-500',
                'merah' => 'bg-rose-600',
                'biru' => 'bg-blue-600',
                default => 'bg-slate-400',
            };
        @endphp
        <div class="p-3 rounded-2xl {{ $cardClasses }} flex flex-col justify-between transition-all duration-200 relative cursor-pointer group hover:-translate-y-1"
             onclick="openDetailMonitoringModal({{ json_encode($item) }})"
             title="{{ $item['kelas'] }}: {{ $item['guru_nama'] }} - {{ $item['mapel'] }} ({{ $item['badge_text'] }})">
            
            <!-- 1. Header Kartu: Nama Kelas & Badge Status Presensi -->
            <div>
                <div class="flex items-center justify-between gap-1.5 pb-2 mb-2 border-b border-black/10">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full {{ $dotWarna }} shrink-0"></span>
                        <span class="text-sm font-black text-slate-950 tracking-tight truncate">
                            {{ $item['kelas'] }}
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $badgeWarna }} shrink-0 shadow-2xs">
                        {{ $item['badge_text'] }}
                    </span>
                </div>

                <!-- 2. Informasi Guru Pengampu -->
                <div class="space-y-1.5">
                    <div class="flex items-start gap-1.5 min-w-0">
                        <i class="bi-person-fill text-xs text-slate-600 shrink-0 mt-0.5"></i>
                        <span class="text-xs font-black leading-snug truncate text-slate-900" title="{{ $item['guru_nama'] }}">
                            {{ $item['guru_nama'] ?: 'Kosong' }}
                        </span>
                    </div>

                    <!-- 3. Mata Pelajaran -->
                    <div class="flex items-start gap-1.5 min-w-0">
                        <i class="bi-book-half text-[11px] text-slate-500 shrink-0 mt-0.5"></i>
                        <span class="text-[11px] font-bold leading-snug text-slate-700 truncate" title="{{ $item['mapel'] }}">
                            {{ $item['mapel'] ?: 'Di Luar Jam KBM' }}
                        </span>
                    </div>

                    @if(!empty($item['guru_pengganti']))
                    <div class="text-[9.5px] font-black text-blue-800 bg-blue-100/90 border border-blue-200 px-1.5 py-0.5 rounded truncate">
                        Inval: {{ $item['guru_pengganti'] }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- 4. Footer Kartu: Jam KBM & Ruangan -->
            <div class="mt-2.5 pt-1.5 border-t border-black/10 text-[10px] text-slate-700 flex items-center justify-between font-black">
                <span class="truncate flex items-center gap-1">
                    <i class="bi-clock text-[10px] text-slate-500"></i>
                    <span>{{ $item['jam_display'] ?: 'Di Luar KBM' }}</span>
                </span>
                @if(!empty($item['ruang']) && $item['ruang'] !== '-')
                <span class="shrink-0 text-slate-900 font-black ml-1 bg-white/90 border border-black/10 px-1.5 py-0.2 rounded shadow-2xs">
                    R.{{ $item['ruang'] }}
                </span>
                @endif
            </div>
        </div>
        @endforeach
    </div>

</div>

<!-- Modal Pop-Up Detail Kelas Interaktif (Jika Diklik) -->
<div id="monitoring-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs hidden">
    <div class="bg-white rounded-3xl p-6 max-w-sm w-full border border-slate-200 shadow-2xl space-y-4 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 id="modal-kelas-title" class="text-base font-black text-slate-900">Detail Kelas</h3>
                <p id="modal-jam-display" class="text-xs text-slate-500 font-semibold">Jam KBM: -</p>
            </div>
            <button type="button" onclick="closeDetailMonitoringModal()" class="p-1.5 rounded-xl hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                <i class="bi-x-lg text-sm"></i>
            </button>
        </div>

        <div class="space-y-2.5 text-xs">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block">Guru Pengampu</span>
                <p id="modal-guru-nama" class="font-black text-slate-900 text-sm">-</p>
                <p id="modal-guru-pengganti" class="text-[11px] text-blue-700 font-bold hidden"></p>
            </div>

            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-black text-slate-400 block">Mata Pelajaran & Ruang</span>
                <p id="modal-mapel-ruang" class="font-black text-slate-900">-</p>
            </div>

            <div class="p-3 rounded-2xl border space-y-1" id="modal-status-box">
                <span class="text-[10px] uppercase font-black block" id="modal-status-label">Status Presensi</span>
                <p id="modal-keterangan" class="font-bold text-slate-800 leading-relaxed">-</p>
            </div>
        </div>

        <div class="pt-2">
            <button type="button" onclick="closeDetailMonitoringModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold text-xs transition shadow-sm">
                Tutup Informasi
            </button>
        </div>
    </div>
</div>

<!-- Script Live Real-Time Clock & Dynamic Period Detector -->
<script>
(function() {
    const PERIOD_CONFIG = {
        reguler: [
            { ke: 1, mulai: '07:00:00', selesai: '07:45:00', label: 'Jam ke-1', waktu: '07:00 - 07:45 WIB' },
            { ke: 2, mulai: '07:45:00', selesai: '08:30:00', label: 'Jam ke-2', waktu: '07:45 - 08:30 WIB' },
            { ke: 3, mulai: '08:30:00', selesai: '09:15:00', label: 'Jam ke-3', waktu: '08:30 - 09:15 WIB' },
            { ke: 4, mulai: '09:15:00', selesai: '10:00:00', label: 'Jam ke-4', waktu: '09:15 - 10:00 WIB' },
            { ke: 0, mulai: '10:00:00', selesai: '10:30:00', label: 'Istirahat 1', waktu: '10:00 - 10:30 WIB', isBreak: true },
            { ke: 5, mulai: '10:30:00', selesai: '11:15:00', label: 'Jam ke-5', waktu: '10:30 - 11:15 WIB' },
            { ke: 6, mulai: '11:15:00', selesai: '12:00:00', label: 'Jam ke-6', waktu: '11:15 - 12:00 WIB' },
            { ke: 0, mulai: '12:00:00', selesai: '12:30:00', label: 'Istirahat 2 (Ishoma)', waktu: '12:00 - 12:30 WIB', isBreak: true },
            { ke: 7, mulai: '12:30:00', selesai: '13:15:00', label: 'Jam ke-7', waktu: '12:30 - 13:15 WIB' },
            { ke: 8, mulai: '13:15:00', selesai: '14:00:00', label: 'Jam ke-8', waktu: '13:15 - 14:00 WIB' }
        ],
        jumat: [
            { ke: 1, mulai: '07:00:00', selesai: '07:30:00', label: 'Jam ke-1', waktu: '07:00 - 07:30 WIB' },
            { ke: 2, mulai: '07:30:00', selesai: '08:00:00', label: 'Jam ke-2', waktu: '07:30 - 08:00 WIB' },
            { ke: 3, mulai: '08:00:00', selesai: '08:30:00', label: 'Jam ke-3', waktu: '08:00 - 08:30 WIB' },
            { ke: 4, mulai: '08:30:00', selesai: '09:00:00', label: 'Jam ke-4', waktu: '09:30 - 09:00 WIB' },
            { ke: 0, mulai: '09:00:00', selesai: '09:30:00', label: 'Istirahat', waktu: '09:00 - 09:30 WIB', isBreak: true },
            { ke: 5, mulai: '09:30:00', selesai: '10:00:00', label: 'Jam ke-5', waktu: '09:30 - 10:00 WIB' },
            { ke: 6, mulai: '10:00:00', selesai: '10:30:00', label: 'Jam ke-6', waktu: '10:00 - 10:30 WIB' }
        ]
    };

    const DAYS_ID = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const MONTHS_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function updateLiveClockAndPeriod() {
        const now = new Date();
        const dayIdx = now.getDay();
        const dayName = DAYS_ID[dayIdx];
        const dateStr = `${dayName}, ${now.getDate()} ${MONTHS_ID[now.getMonth()]} ${now.getFullYear()}`;
        
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        const timeStr = `${hh}:${mm}:${ss}`;
        const clockDisplay = `${hh}:${mm}:${ss} WIB`;

        // Update elements
        const dateEl = document.getElementById('live-monitoring-date');
        const clockEl = document.getElementById('live-monitoring-clock');
        if (dateEl) dateEl.innerText = dateStr;
        if (clockEl) clockEl.innerText = clockDisplay;

        // Determine Period
        const periodLabelEl = document.getElementById('live-period-label');
        const periodBadgeEl = document.getElementById('live-period-badge');

        if (!periodLabelEl || !periodBadgeEl) return;

        if (dayIdx === 0) { // Minggu
            periodLabelEl.innerText = 'Libur Akhir Pekan';
            periodBadgeEl.innerText = 'Hari Libur Sekolah (Minggu)';
            periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700 shadow-2xs';
            return;
        }

        const isJumat = (dayIdx === 5);
        const schedule = isJumat ? PERIOD_CONFIG.jumat : PERIOD_CONFIG.reguler;
        const firstMulai = schedule[0].mulai;
        const lastSelesai = schedule[schedule.length - 1].selesai;

        if (timeStr < firstMulai) {
            periodLabelEl.innerText = 'Persiapan KBM';
            periodBadgeEl.innerText = `Mulai ${firstMulai.substring(0, 5)} WIB`;
            periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-900 border border-blue-200 shadow-2xs';
            return;
        }

        if (timeStr > lastSelesai) {
            periodLabelEl.innerText = 'KBM Selesai';
            periodBadgeEl.innerText = isJumat ? 'KBM Jumat Selesai' : 'KBM Hari Ini Selesai';
            periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-800 shadow-2xs';
            return;
        }

        for (const item of schedule) {
            if (timeStr >= item.mulai && timeStr <= item.selesai) {
                periodLabelEl.innerText = item.label;
                periodBadgeEl.innerText = item.waktu;
                if (item.isBreak) {
                    periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-amber-500 text-slate-950 shadow-2xs';
                } else {
                    periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-emerald-600 text-white shadow-2xs';
                }
                return;
            }
        }

        // Pergantian Jam jeda
        periodLabelEl.innerText = 'Pergantian Jam';
        periodBadgeEl.innerText = 'Jeda Pelajaran';
        periodBadgeEl.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-900 shadow-2xs';
    }

    // Jalankan detik real-time
    updateLiveClockAndPeriod();
    setInterval(updateLiveClockAndPeriod, 1000);

    // Auto-refresh silent data setiap 30 detik
    setInterval(function() {
        refreshMonitoringData(null, true);
    }, 30000);
})();

// Function AJAX Refresh Real-Time
function refreshMonitoringData(btnEl = null, isSilent = false) {
    let icon = null;
    if (btnEl) {
        icon = btnEl.querySelector('i');
        if (icon) icon.classList.add('animate-spin');
    }

    fetch('{{ url('/monitoring-kelas/data') }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.classes) {
                // Update rekap numbers
                if (data.rekap) {
                    const elAda = document.getElementById('rekap-ada-guru');
                    const elIzin = document.getElementById('rekap-izin');
                    const elSakit = document.getElementById('rekap-sakit');
                    const elTugas = document.getElementById('rekap-tugas-luar');
                    if (elAda) elAda.innerText = data.rekap.ada_guru ?? 0;
                    if (elIzin) elIzin.innerText = data.rekap.izin ?? 0;
                    if (elSakit) elSakit.innerText = data.rekap.sakit ?? 0;
                    if (elTugas) elTugas.innerText = data.rekap.tugas_luar ?? 0;
                }

                // Update period Info jika ada dari server
                if (data.periodInfo) {
                    const pLabel = document.getElementById('live-period-label');
                    const pBadge = document.getElementById('live-period-badge');
                    if (pLabel && data.periodInfo.label_jam_ke) pLabel.innerText = data.periodInfo.label_jam_ke;
                    if (pBadge && data.periodInfo.rentang_waktu) {
                        pBadge.innerText = data.periodInfo.rentang_waktu;
                        if (data.periodInfo.badge_class) pBadge.className = 'text-[11px] font-black px-2.5 py-0.5 rounded-full ' + data.periodInfo.badge_class;
                    }
                }

                // Render grid
                const container = document.getElementById('monitoring-grid-container');
                if (container && Array.isArray(data.classes)) {
                    let html = '';
                    data.classes.forEach(item => {
                        let cardClass = 'bg-slate-50/95 border-2 border-slate-300 text-slate-800 hover:bg-white shadow-2xs hover:shadow-xs';
                        let badgeClass = 'bg-slate-600 text-white';
                        let dotClass = 'bg-slate-400';

                        if (item.warna === 'hijau') {
                            cardClass = 'bg-emerald-50/90 border-2 border-emerald-500 text-emerald-950 hover:bg-emerald-100 shadow-xs hover:shadow-md';
                            badgeClass = 'bg-emerald-700 text-white';
                            dotClass = 'bg-emerald-600';
                        } else if (item.warna === 'kuning') {
                            cardClass = 'bg-amber-50/90 border-2 border-amber-400 text-amber-950 hover:bg-amber-100 shadow-xs hover:shadow-md';
                            badgeClass = 'bg-amber-500 text-slate-950';
                            dotClass = 'bg-amber-500';
                        } else if (item.warna === 'merah') {
                            cardClass = 'bg-rose-50/90 border-2 border-rose-400 text-rose-950 hover:bg-rose-100 shadow-xs hover:shadow-md';
                            badgeClass = 'bg-rose-700 text-white';
                            dotClass = 'bg-rose-600';
                        } else if (item.warna === 'biru') {
                            cardClass = 'bg-blue-50/90 border-2 border-blue-400 text-blue-950 hover:bg-blue-100 shadow-xs hover:shadow-md';
                            badgeClass = 'bg-blue-700 text-white';
                            dotClass = 'bg-blue-600';
                        }

                        const rawData = JSON.stringify(item).replace(/"/g, '&quot;');
                        const ruangText = (item.ruang && item.ruang !== '-') ? `<span class="shrink-0 text-slate-900 font-black ml-1 bg-white/90 border border-black/10 px-1.5 py-0.2 rounded shadow-2xs">R.${item.ruang}</span>` : '';
                        const invalBadge = item.guru_pengganti ? `<div class="text-[9.5px] font-black text-blue-800 bg-blue-100/90 border border-blue-200 px-1.5 py-0.5 rounded truncate">Inval: ${item.guru_pengganti}</div>` : '';

                        html += `
                        <div class="p-3 rounded-2xl ${cardClass} flex flex-col justify-between transition-all duration-200 relative cursor-pointer group hover:-translate-y-1"
                             onclick="openDetailMonitoringModal(${rawData})"
                             title="${item.kelas}: ${item.guru_nama} - ${item.mapel} (${item.badge_text})">
                            <div>
                                <div class="flex items-center justify-between gap-1.5 pb-2 mb-2 border-b border-black/10">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full ${dotClass} shrink-0"></span>
                                        <span class="text-sm font-black text-slate-950 tracking-tight truncate">
                                            ${item.kelas}
                                        </span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider ${badgeClass} shrink-0 shadow-2xs">
                                        ${item.badge_text}
                                    </span>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex items-start gap-1.5 min-w-0">
                                        <i class="bi-person-fill text-xs text-slate-600 shrink-0 mt-0.5"></i>
                                        <span class="text-xs font-black leading-snug truncate text-slate-900" title="${item.guru_nama}">
                                            ${item.guru_nama || 'Kosong'}
                                        </span>
                                    </div>
                                    <div class="flex items-start gap-1.5 min-w-0">
                                        <i class="bi-book-half text-[11px] text-slate-500 shrink-0 mt-0.5"></i>
                                        <span class="text-[11px] font-bold leading-snug text-slate-700 truncate" title="${item.mapel}">
                                            ${item.mapel || 'Di Luar Jam KBM'}
                                        </span>
                                    </div>
                                    ${invalBadge}
                                </div>
                            </div>
                            <div class="mt-2.5 pt-1.5 border-t border-black/10 text-[10px] text-slate-700 flex items-center justify-between font-black">
                                <span class="truncate flex items-center gap-1">
                                    <i class="bi-clock text-[10px] text-slate-500"></i>
                                    <span>${item.jam_display || 'Di Luar KBM'}</span>
                                </span>
                                ${ruangText}
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                }
            }
        })
        .catch(err => {
            if (!isSilent) console.error("Gagal menyegarkan monitoring kelas:", err);
        })
        .finally(() => {
            if (icon) icon.classList.remove('animate-spin');
        });
}

// Modal Detail functions
function openDetailMonitoringModal(item) {
    const modal = document.getElementById('monitoring-detail-modal');
    if (!modal) return;

    document.getElementById('modal-kelas-title').innerText = item.kelas || 'Detail Kelas';
    document.getElementById('modal-jam-display').innerText = 'Jam KBM: ' + (item.jam_display || 'Di Luar Jam KBM');
    document.getElementById('modal-guru-nama').innerText = item.guru_nama || '-';

    const penggantiEl = document.getElementById('modal-guru-pengganti');
    if (item.guru_pengganti) {
        penggantiEl.innerText = 'Digantikan Guru Inval: ' + item.guru_pengganti;
        penggantiEl.classList.remove('hidden');
    } else {
        penggantiEl.classList.add('hidden');
    }

    const ruangStr = (item.ruang && item.ruang !== '-') ? ' • R. ' + item.ruang : '';
    document.getElementById('modal-mapel-ruang').innerText = (item.mapel || '-') + ruangStr;
    
    document.getElementById('modal-status-label').innerText = 'Status: ' + (item.status_label || item.badge_text || '-');
    document.getElementById('modal-keterangan').innerText = item.keterangan || '-';

    const box = document.getElementById('modal-status-box');
    if (box) {
        box.className = 'p-3 rounded-2xl border space-y-1 ';
        if (item.warna === 'hijau') box.className += 'bg-emerald-50 border-emerald-300 text-emerald-950 font-bold';
        else if (item.warna === 'kuning') box.className += 'bg-amber-50 border-amber-300 text-amber-950 font-bold';
        else if (item.warna === 'merah') box.className += 'bg-rose-50 border-rose-300 text-rose-950 font-bold';
        else if (item.warna === 'biru') box.className += 'bg-blue-50 border-blue-300 text-blue-950 font-bold';
        else box.className += 'bg-slate-50 border-slate-300 text-slate-900 font-bold';
    }

    modal.classList.remove('hidden');
}

function closeDetailMonitoringModal() {
    const modal = document.getElementById('monitoring-detail-modal');
    if (modal) modal.classList.add('hidden');
}
</script>
