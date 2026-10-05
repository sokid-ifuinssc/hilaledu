{{-- Filter periode: per bulan / per semester / per tahun ajaran --}}
@php
    $mode = $periode['mode'];
    $inputCls = 'px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl font-medium text-xs focus:ring-2 focus:ring-emerald-500 outline-none';
@endphp
<form method="GET" action="{{ $action }}" id="rekapFilterForm" class="flex flex-wrap items-end gap-3 text-xs">
    <div>
        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Periode</label>
        <select name="mode" id="rekapMode" class="{{ $inputCls }}">
            <option value="bulan" {{ $mode === 'bulan' ? 'selected' : '' }}>Per Bulan</option>
            <option value="semester" {{ $mode === 'semester' ? 'selected' : '' }}>Per Semester</option>
            <option value="tahun" {{ $mode === 'tahun' ? 'selected' : '' }}>Per Tahun Ajaran</option>
        </select>
    </div>

    <div data-show="bulan">
        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Bulan</label>
        <input type="month" name="bulan" value="{{ $periode['bulan'] }}" class="{{ $inputCls }}">
    </div>

    <div data-show="semester tahun">
        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tahun Ajaran</label>
        <select name="tahun_ajaran" class="{{ $inputCls }}">
            @foreach($taOptions as $ta)
                <option value="{{ $ta }}" {{ $periode['tahun_ajaran'] === $ta ? 'selected' : '' }}>{{ $ta }}</option>
            @endforeach
        </select>
    </div>

    <div data-show="semester">
        <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Semester</label>
        <select name="semester" class="{{ $inputCls }}">
            <option value="1" {{ $periode['semester'] === '1' ? 'selected' : '' }}>Ganjil (Jul - Des)</option>
            <option value="2" {{ $periode['semester'] === '2' ? 'selected' : '' }}>Genap (Jan - Jun)</option>
        </select>
    </div>

    <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition flex items-center gap-1.5 shadow-sm">
        <i class="bi bi-search"></i><span>Tampilkan</span>
    </button>
</form>

<script>
    (function () {
        var form = document.getElementById('rekapFilterForm');
        var sel = document.getElementById('rekapMode');
        function sync() {
            form.querySelectorAll('[data-show]').forEach(function (box) {
                var on = box.getAttribute('data-show').split(' ').indexOf(sel.value) !== -1;
                box.style.display = on ? '' : 'none';
                box.querySelectorAll('input,select').forEach(function (el) { el.disabled = !on; });
            });
        }
        sel.addEventListener('change', sync);
        sync();
    })();
</script>
