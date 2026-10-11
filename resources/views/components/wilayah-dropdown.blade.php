@props([
    'provinsi'  => 'Jawa Barat',
    'kabupaten' => '',
    'kecamatan' => '',
    'desa'      => '',
    'layout'    => 'grid', // 'grid' (tailwind) or 'bootstrap'
])

@php
    $idPrefix = 'wilayah_' . uniqid();
    $currentProvinsi = old('provinsi', $provinsi ?: 'Jawa Barat');
    $currentKabupaten = old('kabupaten', $kabupaten);
    $currentKecamatan = old('kecamatan', $kecamatan);
    $currentDesa = old('desa', $desa);
@endphp

<style>
    /* Styling Searchable Combobox */
    .wilayah-cb-list {
        position: absolute;
        left: 0;
        right: 0;
        top: 100%;
        margin-top: 4px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.15), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
        max-height: 230px;
        overflow-y: auto;
        z-index: 1050;
        padding: 4px;
        display: none;
    }
    .wilayah-cb-list.show {
        display: block;
    }
    .wilayah-cb-item {
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 500;
        color: #334155;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.15s ease, color 0.15s ease;
    }
    .wilayah-cb-item:hover, .wilayah-cb-item.active {
        background: #ecfdf5;
        color: #047857;
        font-weight: 600;
    }
    .wilayah-cb-highlight {
        color: #059669;
        font-weight: 800;
        text-decoration: underline;
        text-underline-offset: 2px;
    }
    .wilayah-cb-empty {
        padding: 10px 12px;
        font-size: 11px;
        color: #94a3b8;
        text-align: center;
        font-style: italic;
    }
    .wilayah-cb-indicator {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
</style>

@if($layout === 'bootstrap')
<div class="row g-3" id="{{ $idPrefix }}_container">
    {{-- Provinsi --}}
    <div class="col-md-3 position-relative" id="{{ $idPrefix }}_provinsi_wrapper">
        <label class="field-label d-block text-xs font-semibold mb-1">Provinsi</label>
        <div class="position-relative">
            <input type="text" 
                   name="provinsi" 
                   id="{{ $idPrefix }}_provinsi" 
                   value="{{ $currentProvinsi }}" 
                   class="form-control form-control-sm pe-4" 
                   placeholder="Ketik/Cari Provinsi..." 
                   autocomplete="off">
            <span class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted wilayah-cb-indicator" id="{{ $idPrefix }}_provinsi_btn" title="Buka Pilihan">
                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
            </span>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_provinsi_list"></div>
    </div>

    {{-- Kabupaten / Kota --}}
    <div class="col-md-3 position-relative" id="{{ $idPrefix }}_kabupaten_wrapper">
        <label class="field-label d-block text-xs font-semibold mb-1">Kabupaten / Kota</label>
        <div class="position-relative">
            <input type="text" 
                   name="kabupaten" 
                   id="{{ $idPrefix }}_kabupaten" 
                   value="{{ $currentKabupaten }}" 
                   class="form-control form-control-sm pe-4" 
                   placeholder="Ketik/Cari Kab / Kota..." 
                   autocomplete="off">
            <span class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted wilayah-cb-indicator" id="{{ $idPrefix }}_kabupaten_btn" title="Buka Pilihan">
                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
            </span>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_kabupaten_list"></div>
    </div>

    {{-- Kecamatan --}}
    <div class="col-md-3 position-relative" id="{{ $idPrefix }}_kecamatan_wrapper">
        <label class="field-label d-block text-xs font-semibold mb-1">Kecamatan</label>
        <div class="position-relative">
            <input type="text" 
                   name="kecamatan" 
                   id="{{ $idPrefix }}_kecamatan" 
                   value="{{ $currentKecamatan }}" 
                   class="form-control form-control-sm pe-4" 
                   placeholder="Ketik/Cari Kecamatan..." 
                   autocomplete="off">
            <span class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted wilayah-cb-indicator" id="{{ $idPrefix }}_kecamatan_btn" title="Buka Pilihan">
                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
            </span>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_kecamatan_list"></div>
    </div>

    {{-- Desa / Kelurahan --}}
    <div class="col-md-3 position-relative" id="{{ $idPrefix }}_desa_wrapper">
        <label class="field-label d-block text-xs font-semibold mb-1">Desa / Kelurahan</label>
        <div class="position-relative">
            <input type="text" 
                   name="desa" 
                   id="{{ $idPrefix }}_desa" 
                   value="{{ $currentDesa }}" 
                   class="form-control form-control-sm pe-4" 
                   placeholder="Ketik/Cari Desa..." 
                   autocomplete="off">
            <span class="position-absolute end-0 top-50 translate-middle-y me-2 text-muted wilayah-cb-indicator" id="{{ $idPrefix }}_desa_btn" title="Buka Pilihan">
                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
            </span>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_desa_list"></div>
    </div>
</div>
@else
{{-- Layout Grid Tailwind --}}
<div class="contents" id="{{ $idPrefix }}_container">
    {{-- Provinsi --}}
    <div class="relative" id="{{ $idPrefix }}_provinsi_wrapper">
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Provinsi</label>
        <div class="relative">
            <input type="text" 
                   name="provinsi" 
                   id="{{ $idPrefix }}_provinsi" 
                   value="{{ $currentProvinsi }}" 
                   class="w-full pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-xs" 
                   placeholder="Ketik / cari provinsi..." 
                   autocomplete="off">
            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 wilayah-cb-indicator" id="{{ $idPrefix }}_provinsi_btn" title="Lihat Pilihan">
                <i class="bi bi-chevron-down text-xs"></i>
            </button>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_provinsi_list"></div>
    </div>

    {{-- Kabupaten / Kota --}}
    <div class="relative" id="{{ $idPrefix }}_kabupaten_wrapper">
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kabupaten / Kota</label>
        <div class="relative">
            <input type="text" 
                   name="kabupaten" 
                   id="{{ $idPrefix }}_kabupaten" 
                   value="{{ $currentKabupaten }}" 
                   class="w-full pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-xs" 
                   placeholder="Ketik / cari kab / kota..." 
                   autocomplete="off">
            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 wilayah-cb-indicator" id="{{ $idPrefix }}_kabupaten_btn" title="Lihat Pilihan">
                <i class="bi bi-chevron-down text-xs"></i>
            </button>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_kabupaten_list"></div>
    </div>

    {{-- Kecamatan --}}
    <div class="relative" id="{{ $idPrefix }}_kecamatan_wrapper">
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kecamatan</label>
        <div class="relative">
            <input type="text" 
                   name="kecamatan" 
                   id="{{ $idPrefix }}_kecamatan" 
                   value="{{ $currentKecamatan }}" 
                   class="w-full pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-xs" 
                   placeholder="Ketik / cari kecamatan..." 
                   autocomplete="off">
            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 wilayah-cb-indicator" id="{{ $idPrefix }}_kecamatan_btn" title="Lihat Pilihan">
                <i class="bi bi-chevron-down text-xs"></i>
            </button>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_kecamatan_list"></div>
    </div>

    {{-- Desa / Kelurahan --}}
    <div class="relative" id="{{ $idPrefix }}_desa_wrapper">
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Desa / Kelurahan</label>
        <div class="relative">
            <input type="text" 
                   name="desa" 
                   id="{{ $idPrefix }}_desa" 
                   value="{{ $currentDesa }}" 
                   class="w-full pl-3.5 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition shadow-xs" 
                   placeholder="Ketik / cari desa / kelurahan..." 
                   autocomplete="off">
            <button type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 wilayah-cb-indicator" id="{{ $idPrefix }}_desa_btn" title="Lihat Pilihan">
                <i class="bi bi-chevron-down text-xs"></i>
            </button>
        </div>
        <div class="wilayah-cb-list" id="{{ $idPrefix }}_desa_list"></div>
    </div>
</div>
@endif

<script>
(function() {
    // Dataset Wilayah Indonesia
    const W_PROVINSI = @json(\App\Services\WilayahIndonesiaService::getProvinsi());
    const W_KABUPATEN = @json(\App\Services\WilayahIndonesiaService::getKabupatenPerProvinsi());
    const W_KECAMATAN = @json(\App\Services\WilayahIndonesiaService::getKecamatanPerKabupaten());
    const W_DESA = @json(\App\Services\WilayahIndonesiaService::getDesaPerKecamatan());

    // Elemen Input
    const provInput = document.getElementById('{{ $idPrefix }}_provinsi');
    const kabInput  = document.getElementById('{{ $idPrefix }}_kabupaten');
    const kecInput  = document.getElementById('{{ $idPrefix }}_kecamatan');
    const desaInput = document.getElementById('{{ $idPrefix }}_desa');

    // Elemen Dropdown List
    const provList = document.getElementById('{{ $idPrefix }}_provinsi_list');
    const kabList  = document.getElementById('{{ $idPrefix }}_kabupaten_list');
    const kecList  = document.getElementById('{{ $idPrefix }}_kecamatan_list');
    const desaList = document.getElementById('{{ $idPrefix }}_desa_list');

    // Tombol Toggle Dropdown
    const provBtn = document.getElementById('{{ $idPrefix }}_provinsi_btn');
    const kabBtn  = document.getElementById('{{ $idPrefix }}_kabupaten_btn');
    const kecBtn  = document.getElementById('{{ $idPrefix }}_kecamatan_btn');
    const desaBtn = document.getElementById('{{ $idPrefix }}_desa_btn');

    function cleanStr(s) {
        return (s || '').toLowerCase().replace(/^(kabupaten|kab\.|kota|kec\.|kecamatan|desa|kel\.|kelurahan)\s+/i, '').trim();
    }

    /**
     * Algoritma Pencarian Kemiripan Karakter (Similarity Matching)
     * Mendukung:
     * 1. Exact match / Starts with (skor tertinggi)
     * 2. Substring match (mengandung kata/karakter)
     * 3. Fuzzy subsequence match (urutan karakter mirip, misal 'arjwngn' -> 'Arjawinangun')
     */
    function calculateMatch(item, query) {
        if (!query) return { matched: true, score: 0, hlText: item };

        const lowerItem = item.toLowerCase();
        const lowerQ = query.toLowerCase().trim();

        // 1. Starts with
        if (lowerItem.startsWith(lowerQ)) {
            const hl = `<span class="wilayah-cb-highlight">${item.substring(0, lowerQ.length)}</span>${item.substring(lowerQ.length)}`;
            return { matched: true, score: 100 - (item.length - lowerQ.length), hlText: hl };
        }

        // 2. Substring match
        const subIndex = lowerItem.indexOf(lowerQ);
        if (subIndex !== -1) {
            const before = item.substring(0, subIndex);
            const mid = item.substring(subIndex, subIndex + lowerQ.length);
            const after = item.substring(subIndex + lowerQ.length);
            const hl = `${before}<span class="wilayah-cb-highlight">${mid}</span>${after}`;
            return { matched: true, score: 70 - subIndex, hlText: hl };
        }

        // 3. Clean string match (mengabaikan prefix kab/kec)
        const cleanItem = cleanStr(item);
        const cleanQ = cleanStr(query);
        if (cleanItem.includes(cleanQ) && cleanQ.length > 0) {
            return { matched: true, score: 50, hlText: item };
        }

        // 4. Fuzzy subsequence match
        let i = 0, j = 0;
        let hlArr = [];
        while (i < item.length && j < lowerQ.length) {
            if (item[i].toLowerCase() === lowerQ[j]) {
                hlArr.push(`<span class="wilayah-cb-highlight">${item[i]}</span>`);
                j++;
            } else {
                hlArr.push(item[i]);
            }
            i++;
        }
        if (j === lowerQ.length) {
            while (i < item.length) {
                hlArr.push(item[i]);
                i++;
            }
            return { matched: true, score: 30 - (item.length - lowerQ.length), hlText: hlArr.join('') };
        }

        return { matched: false, score: -1, hlText: item };
    }

    /**
     * Dapatkan Dataset Opsi Berdasarkan Pilihan Parent (Cascading)
     */
    function getOptionsFor(type) {
        if (type === 'provinsi') {
            return W_PROVINSI;
        }

        if (type === 'kabupaten') {
            const prov = provInput.value.trim();
            let list = W_KABUPATEN[prov] || [];
            if (list.length === 0) {
                for (let p in W_KABUPATEN) {
                    if (cleanStr(p) === cleanStr(prov)) {
                        list = W_KABUPATEN[p];
                        break;
                    }
                }
            }
            // Fallback default jika provinsi belum dipilih atau kosong
            if (list.length === 0 && (!prov || cleanStr(prov) === 'jawa barat')) {
                list = W_KABUPATEN['Jawa Barat'] || [];
            }
            return list;
        }

        if (type === 'kecamatan') {
            const kab = kabInput.value.trim();
            let list = W_KECAMATAN[kab] || [];
            if (list.length === 0) {
                for (let k in W_KECAMATAN) {
                    if (cleanStr(k) === cleanStr(kab)) {
                        list = W_KECAMATAN[k];
                        break;
                    }
                }
            }
            return list;
        }

        if (type === 'desa') {
            const kec = kecInput.value.trim();
            let list = W_DESA[kec] || [];
            if (list.length === 0) {
                for (let c in W_DESA) {
                    if (cleanStr(c) === cleanStr(kec)) {
                        list = W_DESA[c];
                        break;
                    }
                }
            }
            return list;
        }

        return [];
    }

    /**
     * Render dan Filter Dropdown Secara Dinamis Saat Karakter Diketik
     */
    function renderDropdown(type) {
        const input = document.getElementById(`{{ $idPrefix }}_${type}`);
        const listEl = document.getElementById(`{{ $idPrefix }}_${type}_list`);
        const query = (input.value || '').trim();
        const options = getOptionsFor(type);

        let filtered = [];
        options.forEach(item => {
            const res = calculateMatch(item, query);
            if (res.matched) {
                filtered.push({ item, score: res.score, hlText: res.hlText });
            }
        });

        // Urutkan berdasarkan skor kemiripan tertinggi
        filtered.sort((a, b) => b.score - a.score);

        listEl.innerHTML = '';

        if (filtered.length === 0) {
            const emptyEl = document.createElement('div');
            emptyEl.className = 'wilayah-cb-empty';
            emptyEl.innerHTML = query.length > 0
                ? `<i class="bi bi-info-circle me-1"></i> Tidak ada saran kemiripan. Nilai <strong>"${query}"</strong> akan tetap disimpan sebagai teks kustom.`
                : 'Pilih wilayah tingkat di atasnya terlebih dahulu.';
            listEl.appendChild(emptyEl);
        } else {
            // Batasi tampilan maksimal 40 item agar render cepat
            filtered.slice(0, 40).forEach((match, idx) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'wilayah-cb-item' + (idx === 0 && query.length > 0 ? ' active' : '');
                itemEl.innerHTML = `<span>${match.hlText}</span><i class="bi bi-arrow-right-short text-slate-300" style="font-size: 14px;"></i>`;
                
                // Event klik pilihan
                itemEl.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    selectItem(type, match.item);
                });

                listEl.appendChild(itemEl);
            });
        }

        listEl.classList.add('show');
    }

    /**
     * Pilih Item dan Lakukan Cascading Otomatis
     */
    function selectItem(type, value) {
        const input = document.getElementById(`{{ $idPrefix }}_${type}`);
        const listEl = document.getElementById(`{{ $idPrefix }}_${type}_list`);

        input.value = value;
        listEl.classList.remove('show');

        // Cascading ke elemen bawahan
        if (type === 'provinsi') {
            kabInput.value = '';
            kecInput.value = '';
            desaInput.value = '';
            kabInput.focus();
            renderDropdown('kabupaten');
        } else if (type === 'kabupaten') {
            kecInput.value = '';
            desaInput.value = '';
            kecInput.focus();
            renderDropdown('kecamatan');
        } else if (type === 'kecamatan') {
            desaInput.value = '';
            desaInput.focus();
            renderDropdown('desa');
        }
    }

    /**
     * Pasang Event Listener pada Setiap Input Combobox
     */
    ['provinsi', 'kabupaten', 'kecamatan', 'desa'].forEach(type => {
        const input = document.getElementById(`{{ $idPrefix }}_${type}`);
        const listEl = document.getElementById(`{{ $idPrefix }}_${type}_list`);
        const btn = document.getElementById(`{{ $idPrefix }}_${type}_btn`);

        // Saat input diketik -> filter kemiripan otomatis
        input.addEventListener('input', function() {
            renderDropdown(type);
        });

        // Saat input difokuskan -> buka opsi
        input.addEventListener('focus', function() {
            renderDropdown(type);
        });

        // Toggle via tombol ikon panah di samping
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (listEl.classList.contains('show')) {
                    listEl.classList.remove('show');
                } else {
                    input.focus();
                    renderDropdown(type);
                }
            });
        }

        // Navigasi Keyboard (Arrow Down, Arrow Up, Enter, Escape)
        input.addEventListener('keydown', function(e) {
            const items = listEl.querySelectorAll('.wilayah-cb-item');
            if (!items.length) return;

            let activeIdx = -1;
            items.forEach((it, idx) => {
                if (it.classList.contains('active')) activeIdx = idx;
            });

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!listEl.classList.contains('show')) {
                    listEl.classList.add('show');
                    return;
                }
                const nextIdx = (activeIdx + 1) % items.length;
                items.forEach(it => it.classList.remove('active'));
                items[nextIdx].classList.add('active');
                items[nextIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prevIdx = activeIdx <= 0 ? items.length - 1 : activeIdx - 1;
                items.forEach(it => it.classList.remove('active'));
                items[prevIdx].classList.add('active');
                items[prevIdx].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                if (listEl.classList.contains('show') && activeIdx >= 0) {
                    e.preventDefault();
                    // Ambil teks dari item yang aktif
                    const selectedText = items[activeIdx].textContent.trim();
                    selectItem(type, selectedText);
                }
            } else if (e.key === 'Escape') {
                listEl.classList.remove('show');
            }
        });

        // Tutup saat blur
        input.addEventListener('blur', function() {
            // Beri timeout kecil agar event mousedown pada item dropdown sempat terpicu
            setTimeout(() => {
                listEl.classList.remove('show');
            }, 200);
        });
    });

    // Tutup seluruh dropdown jika klik di luar container
    document.addEventListener('click', function(e) {
        const container = document.getElementById('{{ $idPrefix }}_container');
        if (container && !container.contains(e.target)) {
            provList.classList.remove('show');
            kabList.classList.remove('show');
            kecList.classList.remove('show');
            desaList.classList.remove('show');
        }
    });
})();
</script>
