{{--
    Link sidebar Rekap Kehadiran.
    Params:
      $mode : 'nav' (item sidebar utama) | 'sub' (item submenu dropdown)
      $self : true = tampilkan "Rekap Kehadiran Saya" (guru/tendik)
    Link "Daftar Hadir Pegawai" & "Rekap Mengajar Guru" hanya tampil bila user berhak (Kurikulum, Kepala Sekolah, Bendahara/Payroll).
--}}
@php
    $rkUser = auth()->user();
    $rkMode = $mode ?? 'nav';
    $rkItems = [];
    if (($self ?? false) && in_array($rkUser->role, ['guru', 'tendik'], true)) {
        $rkItems[] = ['rekap-kehadiran.saya', 'rekap-kehadiran.saya*', 'bi-calendar-check-fill', '#10b981', 'Rekap Kehadiran Saya'];
    }
    if ($rkUser->canViewRekapKehadiran()) {
        $rkItems[] = ['rekap-kehadiran.pegawai', 'rekap-kehadiran.pegawai*', 'bi-people-fill', '#0ea5e9', 'Daftar Hadir Pegawai'];
        $rkItems[] = ['rekap-kehadiran.mengajar', 'rekap-kehadiran.mengajar*', 'bi-person-video3', '#8b5cf6', 'Rekap Mengajar Guru'];
    }
@endphp
@foreach($rkItems as [$rkRoute, $rkPattern, $rkIcon, $rkColor, $rkLabel])
    @if($rkMode === 'sub')
        <li><a href="{{ route($rkRoute) }}" class="{{ request()->routeIs($rkPattern) ? 'active' : '' }}"><i class="bi {{ $rkIcon }} me-1" style="color: {{ $rkColor }};"></i> {{ $rkLabel }}</a></li>
    @else
        <li class="sidebar-nav-item"><a href="{{ route($rkRoute) }}" class="sidebar-nav-link {{ request()->routeIs($rkPattern) ? 'active' : '' }}"><i class="bi {{ $rkIcon }}" style="color:{{ $rkColor }};"></i><span>{{ $rkLabel }}</span></a></li>
    @endif
@endforeach
