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

@if($layout === 'bootstrap')
<div class="row g-3" id="{{ $idPrefix }}_container">
    {{-- Provinsi --}}
    <div class="col-md-3">
        <label class="field-label">Provinsi</label>
        <select name="provinsi" id="{{ $idPrefix }}_provinsi" class="form-input form-select" onchange="{{ $idPrefix }}_onProvinsiChange()">
            <option value="">-- Pilih Provinsi --</option>
        </select>
        <div id="{{ $idPrefix }}_provinsi_manual_box" class="mt-1 d-none">
            <input type="text" id="{{ $idPrefix }}_provinsi_manual" class="form-input form-control form-control-sm" placeholder="Ketik nama provinsi">
        </div>
    </div>

    {{-- Kabupaten / Kota --}}
    <div class="col-md-3">
        <label class="field-label">Kabupaten / Kota</label>
        <select name="kabupaten" id="{{ $idPrefix }}_kabupaten" class="form-input form-select" onchange="{{ $idPrefix }}_onKabupatenChange()">
            <option value="">-- Pilih Kab / Kota --</option>
        </select>
        <div id="{{ $idPrefix }}_kabupaten_manual_box" class="mt-1 d-none">
            <input type="text" id="{{ $idPrefix }}_kabupaten_manual" class="form-input form-control form-control-sm" placeholder="Ketik nama kabupaten / kota">
        </div>
    </div>

    {{-- Kecamatan --}}
    <div class="col-md-3">
        <label class="field-label">Kecamatan</label>
        <select name="kecamatan" id="{{ $idPrefix }}_kecamatan" class="form-input form-select" onchange="{{ $idPrefix }}_onKecamatanChange()">
            <option value="">-- Pilih Kecamatan --</option>
        </select>
        <div id="{{ $idPrefix }}_kecamatan_manual_box" class="mt-1 d-none">
            <input type="text" id="{{ $idPrefix }}_kecamatan_manual" class="form-input form-control form-control-sm" placeholder="Ketik nama kecamatan">
        </div>
    </div>

    {{-- Desa / Kelurahan --}}
    <div class="col-md-3">
        <label class="field-label">Desa / Kelurahan</label>
        <select name="desa" id="{{ $idPrefix }}_desa" class="form-input form-select" onchange="{{ $idPrefix }}_onDesaChange()">
            <option value="">-- Pilih Desa / Kelurahan --</option>
        </select>
        <div id="{{ $idPrefix }}_desa_manual_box" class="mt-1 d-none">
            <input type="text" id="{{ $idPrefix }}_desa_manual" class="form-input form-control form-control-sm" placeholder="Ketik nama desa / kelurahan">
        </div>
    </div>
</div>
@else
{{-- Layout Grid Tailwind (untuk profil guru) --}}
<div class="contents" id="{{ $idPrefix }}_container">
    {{-- Provinsi --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Provinsi</label>
        <select name="provinsi" id="{{ $idPrefix }}_provinsi" 
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition"
                onchange="{{ $idPrefix }}_onProvinsiChange()">
            <option value="">-- Pilih Provinsi --</option>
        </select>
        <div id="{{ $idPrefix }}_provinsi_manual_box" class="mt-1.5 hidden">
            <input type="text" id="{{ $idPrefix }}_provinsi_manual" class="w-full px-3 py-1.5 bg-white border border-purple-300 rounded-lg text-xs" placeholder="Ketik nama provinsi">
        </div>
    </div>

    {{-- Kabupaten / Kota --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kabupaten / Kota</label>
        <select name="kabupaten" id="{{ $idPrefix }}_kabupaten" 
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition"
                onchange="{{ $idPrefix }}_onKabupatenChange()">
            <option value="">-- Pilih Kab / Kota --</option>
        </select>
        <div id="{{ $idPrefix }}_kabupaten_manual_box" class="mt-1.5 hidden">
            <input type="text" id="{{ $idPrefix }}_kabupaten_manual" class="w-full px-3 py-1.5 bg-white border border-purple-300 rounded-lg text-xs" placeholder="Ketik nama kabupaten / kota">
        </div>
    </div>

    {{-- Kecamatan --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Kecamatan</label>
        <select name="kecamatan" id="{{ $idPrefix }}_kecamatan" 
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition"
                onchange="{{ $idPrefix }}_onKecamatanChange()">
            <option value="">-- Pilih Kecamatan --</option>
        </select>
        <div id="{{ $idPrefix }}_kecamatan_manual_box" class="mt-1.5 hidden">
            <input type="text" id="{{ $idPrefix }}_kecamatan_manual" class="w-full px-3 py-1.5 bg-white border border-purple-300 rounded-lg text-xs" placeholder="Ketik nama kecamatan">
        </div>
    </div>

    {{-- Desa / Kelurahan --}}
    <div>
        <label class="block text-xs font-bold text-slate-700 mb-1.5">Desa / Kelurahan</label>
        <select name="desa" id="{{ $idPrefix }}_desa" 
                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition"
                onchange="{{ $idPrefix }}_onDesaChange()">
            <option value="">-- Pilih Desa / Kelurahan --</option>
        </select>
        <div id="{{ $idPrefix }}_desa_manual_box" class="mt-1.5 hidden">
            <input type="text" id="{{ $idPrefix }}_desa_manual" class="w-full px-3 py-1.5 bg-white border border-purple-300 rounded-lg text-xs" placeholder="Ketik nama desa / kelurahan">
        </div>
    </div>
</div>
@endif

<script>
(function() {
    // Data Wilayah Embedded
    const W_PROVINSI = @json(\App\Services\WilayahIndonesiaService::getProvinsi());
    const W_KABUPATEN = @json(\App\Services\WilayahIndonesiaService::getKabupatenPerProvinsi());
    const W_KECAMATAN = @json(\App\Services\WilayahIndonesiaService::getKecamatanPerKabupaten());
    const W_DESA = @json(\App\Services\WilayahIndonesiaService::getDesaPerKecamatan());

    const initVal = {
        provinsi: @json($currentProvinsi),
        kabupaten: @json($currentKabupaten),
        kecamatan: @json($currentKecamatan),
        desa: @json($currentDesa)
    };

    const provSel = document.getElementById('{{ $idPrefix }}_provinsi');
    const kabSel  = document.getElementById('{{ $idPrefix }}_kabupaten');
    const kecSel  = document.getElementById('{{ $idPrefix }}_kecamatan');
    const desaSel = document.getElementById('{{ $idPrefix }}_desa');

    const isBs = '{{ $layout }}' === 'bootstrap';
    const hideClass = isBs ? 'd-none' : 'hidden';

    // Normalizer string
    function cleanStr(s) {
        return (s || '').toLowerCase().replace(/^(kabupaten|kab\.|kota)\s+/i, '').trim();
    }

    // 1. Populate Provinsi
    function initProvinsi() {
        provSel.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
        W_PROVINSI.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p;
            opt.textContent = p;
            if (cleanStr(p) === cleanStr(initVal.provinsi) || p === initVal.provinsi) {
                opt.selected = true;
            }
            provSel.appendChild(opt);
        });

        // Opsi Manual
        const optMan = document.createElement('option');
        optMan.value = '__manual__';
        optMan.textContent = '+ Ketik Provinsi Lainnya...';
        provSel.appendChild(optMan);

        // Jika value saat ini tidak ada di daftar
        if (initVal.provinsi && !W_PROVINSI.some(p => cleanStr(p) === cleanStr(initVal.provinsi))) {
            const customOpt = document.createElement('option');
            customOpt.value = initVal.provinsi;
            customOpt.textContent = initVal.provinsi;
            customOpt.selected = true;
            provSel.insertBefore(customOpt, optMan);
        }

        populateKabupaten();
    }

    // 2. Populate Kabupaten
    function populateKabupaten() {
        const prov = provSel.value;
        kabSel.innerHTML = '<option value="">-- Pilih Kab / Kota --</option>';

        let list = W_KABUPATEN[prov] || [];
        if (prov === 'Jawa Barat' && list.length === 0) {
            list = W_KABUPATEN['Jawa Barat'] || [];
        }

        let matched = false;
        list.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k;
            opt.textContent = k;
            if (initVal.kabupaten && (cleanStr(k) === cleanStr(initVal.kabupaten) || k === initVal.kabupaten)) {
                opt.selected = true;
                matched = true;
            }
            kabSel.appendChild(opt);
        });

        // Opsi Manual
        const optMan = document.createElement('option');
        optMan.value = '__manual__';
        optMan.textContent = '+ Ketik Kab/Kota Lainnya...';
        kabSel.appendChild(optMan);

        if (initVal.kabupaten && !matched) {
            const customOpt = document.createElement('option');
            customOpt.value = initVal.kabupaten;
            customOpt.textContent = initVal.kabupaten;
            customOpt.selected = true;
            kabSel.insertBefore(customOpt, optMan);
        }

        populateKecamatan();
    }

    // 3. Populate Kecamatan
    function populateKecamatan() {
        const kab = kabSel.value;
        kecSel.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';

        // Cari di data kecamatan dengan pencocokan nama
        let list = W_KECAMATAN[kab] || [];
        if (list.length === 0) {
            for (let k in W_KECAMATAN) {
                if (cleanStr(k) === cleanStr(kab)) {
                    list = W_KECAMATAN[k];
                    break;
                }
            }
        }

        let matched = false;
        list.forEach(kc => {
            const opt = document.createElement('option');
            opt.value = kc;
            opt.textContent = kc;
            if (initVal.kecamatan && (cleanStr(kc) === cleanStr(initVal.kecamatan) || kc === initVal.kecamatan)) {
                opt.selected = true;
                matched = true;
            }
            kecSel.appendChild(opt);
        });

        // Opsi Manual
        const optMan = document.createElement('option');
        optMan.value = '__manual__';
        optMan.textContent = '+ Ketik Kecamatan Lainnya...';
        kecSel.appendChild(optMan);

        if (initVal.kecamatan && !matched) {
            const customOpt = document.createElement('option');
            customOpt.value = initVal.kecamatan;
            customOpt.textContent = initVal.kecamatan;
            customOpt.selected = true;
            kecSel.insertBefore(customOpt, optMan);
        }

        populateDesa();
    }

    // 4. Populate Desa
    function populateDesa() {
        const kec = kecSel.value;
        desaSel.innerHTML = '<option value="">-- Pilih Desa / Kelurahan --</option>';

        let list = W_DESA[kec] || [];
        if (list.length === 0) {
            for (let c in W_DESA) {
                if (cleanStr(c) === cleanStr(kec)) {
                    list = W_DESA[c];
                    break;
                }
            }
        }

        let matched = false;
        list.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (initVal.desa && (cleanStr(d) === cleanStr(initVal.desa) || d === initVal.desa)) {
                opt.selected = true;
                matched = true;
            }
            desaSel.appendChild(opt);
        });

        // Opsi Manual
        const optMan = document.createElement('option');
        optMan.value = '__manual__';
        optMan.textContent = '+ Ketik Desa Lainnya...';
        desaSel.appendChild(optMan);

        if (initVal.desa && !matched) {
            const customOpt = document.createElement('option');
            customOpt.value = initVal.desa;
            customOpt.textContent = initVal.desa;
            customOpt.selected = true;
            desaSel.insertBefore(customOpt, optMan);
        }
    }

    // Handlers
    window['{{ $idPrefix }}_onProvinsiChange'] = function() {
        const box = document.getElementById('{{ $idPrefix }}_provinsi_manual_box');
        const input = document.getElementById('{{ $idPrefix }}_provinsi_manual');
        if (provSel.value === '__manual__') {
            box.classList.remove(hideClass);
            input.focus();
            input.oninput = function() { provSel.value = input.value; };
        } else {
            box.classList.add(hideClass);
        }
        initVal.kabupaten = '';
        initVal.kecamatan = '';
        initVal.desa = '';
        populateKabupaten();
    };

    window['{{ $idPrefix }}_onKabupatenChange'] = function() {
        const box = document.getElementById('{{ $idPrefix }}_kabupaten_manual_box');
        const input = document.getElementById('{{ $idPrefix }}_kabupaten_manual');
        if (kabSel.value === '__manual__') {
            box.classList.remove(hideClass);
            input.focus();
            input.oninput = function() { kabSel.value = input.value; };
        } else {
            box.classList.add(hideClass);
        }
        initVal.kecamatan = '';
        initVal.desa = '';
        populateKecamatan();
    };

    window['{{ $idPrefix }}_onKecamatanChange'] = function() {
        const box = document.getElementById('{{ $idPrefix }}_kecamatan_manual_box');
        const input = document.getElementById('{{ $idPrefix }}_kecamatan_manual');
        if (kecSel.value === '__manual__') {
            box.classList.remove(hideClass);
            input.focus();
            input.oninput = function() { kecSel.value = input.value; };
        } else {
            box.classList.add(hideClass);
        }
        initVal.desa = '';
        populateDesa();
    };

    window['{{ $idPrefix }}_onDesaChange'] = function() {
        const box = document.getElementById('{{ $idPrefix }}_desa_manual_box');
        const input = document.getElementById('{{ $idPrefix }}_desa_manual');
        if (desaSel.value === '__manual__') {
            box.classList.remove(hideClass);
            input.focus();
            input.oninput = function() { desaSel.value = input.value; };
        } else {
            box.classList.add(hideClass);
        }
    };

    // Jalankan inisialisasi awal
    initProvinsi();
})();
</script>
