<?php

namespace App\Services;

use App\Models\KalenderAkademik;
use App\Models\KalenderAkademikEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleHolidaySyncService
{
    /**
     * Sinkronkan hari libur nasional & keagamaan dari Google Calendar / Data Resmi Indonesia
     */
    public function syncHolidays(KalenderAkademik $kalender): array
    {
        $startDate = Carbon::parse($kalender->tanggal_mulai);
        $endDate = Carbon::parse($kalender->tanggal_selesai);

        $holidays = $this->fetchHolidays($startDate->year, $endDate->year);
        $added = 0;
        $updated = 0;

        foreach ($holidays as $h) {
            $hStart = Carbon::parse($h['date']);
            $hEnd = isset($h['end_date']) ? Carbon::parse($h['end_date']) : $hStart;

            // Pastikan libur berada dalam rentang tahun ajaran
            if ($hStart->lt($startDate) || $hStart->gt($endDate)) {
                continue;
            }

            // Tentukan semester: Bulan 7-12 = Semester 1, Bulan 1-7 = Semester 2
            $semester = ($hStart->month >= 7) ? '1' : '2';

            $event = KalenderAkademikEvent::updateOrCreate(
                [
                    'kalender_akademik_id' => $kalender->id,
                    'tanggal_mulai'        => $hStart->format('Y-m-d'),
                    'tanggal_selesai'      => $hEnd->format('Y-m-d'),
                    'sumber'               => 'google_calendar',
                ],
                [
                    'semester'        => $semester,
                    'judul_kegiatan'  => $h['name'],
                    'kategori'        => $h['is_libur'] ? 'libur_nasional' : 'kegiatan_sekolah',
                    'warna_bg'        => $h['warna'] ?? 'red',
                    'is_libur'        => $h['is_libur'] ?? true,
                    'keterangan'      => $h['description'] ?? 'Hari Libur Nasional / Keagamaan Resmi',
                    'google_event_id' => $h['id'] ?? ('id_holiday_' . $hStart->format('Ymd')),
                ]
            );

            if ($event->wasRecentlyCreated) {
                $added++;
            } else {
                $updated++;
            }
        }

        return [
            'success' => true,
            'added'   => $added,
            'updated' => $updated,
            'total'   => count($holidays),
        ];
    }

    /**
     * Bersihkan event lama yang salah/tercampur dan sinkronkan ulang secara bersih
     */
    public function cleanAndResync(KalenderAkademik $kalender): array
    {
        $kalender->events()->whereIn('sumber', ['google_calendar', 'disdik_jabar'])->delete();
        $holidayRes = $this->syncHolidays($kalender);
        $disdikCount = $this->seedDisdikJabarOfficialEvents($kalender);

        return [
            'holidays' => $holidayRes,
            'disdik'   => $disdikCount,
        ];
    }

    /**
     * Inisialisasi Agenda Pendidikan Resmi Jawa Barat (Sesuai SK Disdik Jabar)
     * Dibuat 100% dinamis mengikuti Tahun Ajaran Kalender ($y1 dan $y2)
     */
    public function seedDisdikJabarOfficialEvents(KalenderAkademik $kalender): int
    {
        $parts = explode('/', $kalender->tahun_ajaran);
        $y1 = (int)($parts[0] ?? Carbon::parse($kalender->tanggal_mulai)->year);
        $y2 = (int)($parts[1] ?? ($y1 + 1));

        $events = [
            // SEMESTER 1 (Tahun $y1)
            [
                'start' => "{$y1}-07-15", 'end' => "{$y1}-07-15", 'semester' => '1',
                'judul' => 'Hari Pertama Masuk Sekolah',
                'kategori' => 'hari_efektif_khusus', 'warna' => 'green', 'is_libur' => false,
                'ket' => "Awal KBM Semester 1 Tahun Ajaran {$kalender->tahun_ajaran}",
            ],
            [
                'start' => "{$y1}-07-15", 'end' => "{$y1}-07-21", 'semester' => '1',
                'judul' => 'Pengenalan Lingkungan Sekolah (MPLS)',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'yellow', 'is_libur' => false,
                'ket' => 'Masa Pengenalan Lingkungan Sekolah Peserta Didik Baru Kelas X',
            ],
            [
                'start' => "{$y1}-08-14", 'end' => "{$y1}-08-14", 'semester' => '1',
                'judul' => 'Kegiatan Hari Pramuka Nasional',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'yellow', 'is_libur' => false,
                'ket' => 'Peringatan Hari Pramuka',
            ],
            [
                'start' => "{$y1}-08-17", 'end' => "{$y1}-08-17", 'semester' => '1',
                'judul' => 'Libur Hari Proklamasi Kemerdekaan Republik Indonesia',
                'kategori' => 'libur_nasional', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'Peringatan HUT Proklamasi Kemerdekaan Republik Indonesia',
            ],
            [
                'start' => "{$y1}-10-26", 'end' => "{$y1}-11-08", 'semester' => '1',
                'judul' => 'Pelaksanaan Tes Kompetensi Akademik (TKA) Gelombang 1 & 2',
                'kategori' => 'ujian_asesmen', 'warna' => 'blue', 'is_libur' => false,
                'ket' => 'Pemetaan Kompetensi Akademik Siswa SMK',
            ],
            [
                'start' => "{$y1}-11-25", 'end' => "{$y1}-11-25", 'semester' => '1',
                'judul' => 'Hari Guru Nasional & HUT PGRI',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'green', 'is_libur' => false,
                'ket' => 'Peringatan Hari Guru Nasional & HUT PGRI',
            ],
            [
                'start' => "{$y1}-11-30", 'end' => "{$y1}-12-11", 'semester' => '1',
                'judul' => 'Asesmen Sumatif Akhir Semester (ASAS) Ganjil',
                'kategori' => 'ujian_asesmen', 'warna' => 'blue', 'is_libur' => false,
                'ket' => 'Penilaian Akhir Semester 1 Teori dan Praktik Kejuruan',
            ],
            [
                'start' => "{$y1}-12-23", 'end' => "{$y1}-12-23", 'semester' => '1',
                'judul' => 'Penetapan & Pembagian Rapor Semester 1',
                'kategori' => 'pembagian_rapor', 'warna' => 'cyan', 'is_libur' => false,
                'ket' => 'Penyerahan Laporan Hasil Belajar (LHB) kepada Orang Tua Siswa',
            ],
            [
                'start' => "{$y1}-12-24", 'end' => "{$y1}-12-25", 'semester' => '1',
                'judul' => 'Cuti Bersama & Libur Hari Raya Natal',
                'kategori' => 'libur_nasional', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'Hari Raya Natal',
            ],
            [
                'start' => "{$y1}-12-28", 'end' => "{$y2}-01-08", 'semester' => '1',
                'judul' => 'Libur Akhir Semester 1',
                'kategori' => 'libur_semester', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'Libur Pembelajaran Semester Ganjil',
            ],

            // SEMESTER 2 (Tahun $y2)
            [
                'start' => "{$y2}-01-01", 'end' => "{$y2}-01-01", 'semester' => '2',
                'judul' => "Libur Tahun Baru Masehi {$y2}",
                'kategori' => 'libur_nasional', 'warna' => 'red', 'is_libur' => true,
                'ket' => "Tahun Baru Masehi {$y2}",
            ],
            [
                'start' => "{$y2}-01-11", 'end' => "{$y2}-01-11", 'semester' => '2',
                'judul' => 'Hari Pertama Masuk Sekolah Semester 2',
                'kategori' => 'hari_efektif_khusus', 'warna' => 'green', 'is_libur' => false,
                'ket' => 'Awal KBM Semester Genap',
            ],
            [
                'start' => "{$y2}-02-15", 'end' => "{$y2}-03-05", 'semester' => '2',
                'judul' => 'Kegiatan Penumbuhan Budi Pekerti / SmartTren Ramadhan',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'yellow', 'is_libur' => false,
                'ket' => 'Pembinaan Karakter & Keagamaan Siswa',
            ],
            [
                'start' => "{$y2}-03-29", 'end' => "{$y2}-04-16", 'semester' => '2',
                'judul' => 'Asesmen Sumatif Akhir Jenjang (ASAJ) Kelas XII',
                'kategori' => 'ujian_asesmen', 'warna' => 'blue', 'is_libur' => false,
                'ket' => 'Ujian Akhir Jenjang Kelulusan Kelas XII SMK',
            ],
            [
                'start' => "{$y2}-04-19", 'end' => "{$y2}-04-30", 'semester' => '2',
                'judul' => 'Uji Kompetensi Keahlian (UKK) Siswa SMK',
                'kategori' => 'ujian_asesmen', 'warna' => 'purple', 'is_libur' => false,
                'ket' => 'Sertifikasi Keahlian Praktik Kejuruan Siswa Tingkat Akhir',
            ],
            [
                'start' => "{$y2}-05-01", 'end' => "{$y2}-05-01", 'semester' => '2',
                'judul' => 'Libur Hari Buruh Internasional',
                'kategori' => 'libur_nasional', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'May Day',
            ],
            [
                'start' => "{$y2}-05-02", 'end' => "{$y2}-05-02", 'semester' => '2',
                'judul' => 'Peringatan Hari Pendidikan Nasional (Hardiknas)',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'green', 'is_libur' => false,
                'ket' => 'Upacara & Peringatan Hari Pendidikan Nasional',
            ],
            [
                'start' => "{$y2}-05-20", 'end' => "{$y2}-05-20", 'semester' => '2',
                'judul' => 'Peringatan Hari Kebangkitan Nasional (Harkitnas)',
                'kategori' => 'kegiatan_sekolah', 'warna' => 'green', 'is_libur' => false,
                'ket' => 'Upacara Hari Kebangkitan Nasional',
            ],
            [
                'start' => "{$y2}-06-01", 'end' => "{$y2}-06-01", 'semester' => '2',
                'judul' => 'Libur Hari Lahir Pancasila',
                'kategori' => 'libur_nasional', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'Peringatan Hari Lahir Pancasila',
            ],
            [
                'start' => "{$y2}-06-07", 'end' => "{$y2}-06-18", 'semester' => '2',
                'judul' => 'Asesmen Sumatif Akhir Tahun (ASAT) Kelas X & XI',
                'kategori' => 'ujian_asesmen', 'warna' => 'blue', 'is_libur' => false,
                'ket' => 'Penilaian Kenaikan Kelas Teori dan Praktik Kejuruan',
            ],
            [
                'start' => "{$y2}-06-25", 'end' => "{$y2}-06-25", 'semester' => '2',
                'judul' => 'Penetapan & Pembagian Rapor Semester 2',
                'kategori' => 'pembagian_rapor', 'warna' => 'cyan', 'is_libur' => false,
                'ket' => 'Penyerahan Buku Rapor Kenaikan Kelas kepada Orang Tua Siswa',
            ],
            [
                'start' => "{$y2}-06-28", 'end' => "{$y2}-07-09", 'semester' => '2',
                'judul' => "Libur Akhir Tahun Ajaran {$kalender->tahun_ajaran}",
                'kategori' => 'libur_semester', 'warna' => 'red', 'is_libur' => true,
                'ket' => 'Libur Kenaikan Kelas & Persiapan Tahun Ajaran Baru',
            ],
        ];

        $inserted = 0;
        foreach ($events as $ev) {
            KalenderAkademikEvent::updateOrCreate(
                [
                    'kalender_akademik_id' => $kalender->id,
                    'tanggal_mulai'        => $ev['start'],
                    'tanggal_selesai'      => $ev['end'],
                    'judul_kegiatan'       => $ev['judul'],
                ],
                [
                    'semester'        => $ev['semester'],
                    'kategori'        => $ev['kategori'],
                    'warna_bg'        => $ev['warna'],
                    'is_libur'        => $ev['is_libur'],
                    'keterangan'      => $ev['ket'],
                    'sumber'          => 'disdik_jabar',
                ]
            );
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Dapatkan daftar hari libur resmi Indonesia (Google Calendar fallback dataset akurat)
     */
    protected function fetchHolidays(int $yearStart, int $yearEnd): array
    {
        // 1. Coba panggil Google Calendar iCal public feed jika koneksi internet tersedia
        try {
            $response = Http::timeout(4)->get('https://calendar.google.com/calendar/ical/id.indonesian%23holiday%40group.v.calendar.google.com/public/basic.ics');
            if ($response->successful()) {
                $parsed = $this->parseIcsContent($response->body(), $yearStart, $yearEnd);
                if (!empty($parsed)) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::info('Google Calendar fetch timed out or offline, using robust Indonesian holiday dictionary.');
        }

        // 2. Fallback kamus hari libur nasional Indonesia resmi
        return $this->getIndonesianHolidayDictionary($yearStart, $yearEnd);
    }

    /**
     * Parsing format ICS iCal dari Google Calendar
     */
    protected function parseIcsContent(string $icsContent, int $yearStart, int $yearEnd): array
    {
        $events = [];
        $lines = explode("\n", str_replace("\r", "", $icsContent));

        $inEvent = false;
        $curSummary = '';
        $curStart = '';
        $curEnd = '';

        foreach ($lines as $line) {
            if (trim($line) === 'BEGIN:VEVENT') {
                $inEvent = true;
                $curSummary = '';
                $curStart = '';
                $curEnd = '';
            } elseif (trim($line) === 'END:VEVENT') {
                if ($inEvent && !empty($curStart) && !empty($curSummary)) {
                    $year = (int) substr($curStart, 0, 4);
                    if ($year >= $yearStart && $year <= $yearEnd) {
                        $events[] = [
                            'date'        => date('Y-m-d', strtotime($curStart)),
                            'end_date'    => !empty($curEnd) ? date('Y-m-d', strtotime($curEnd)) : date('Y-m-d', strtotime($curStart)),
                            'name'        => $curSummary,
                            'is_libur'    => true,
                            'warna'       => 'red',
                            'description' => 'Hari Libur Nasional Indonesia (Google Calendar)',
                        ];
                    }
                }
                $inEvent = false;
            } elseif ($inEvent) {
                if (str_starts_with($line, 'SUMMARY:')) {
                    $curSummary = trim(substr($line, 8));
                } elseif (str_starts_with($line, 'DTSTART;VALUE=DATE:') || str_starts_with($line, 'DTSTART:')) {
                    $parts = explode(':', $line);
                    $curStart = trim($parts[1] ?? '');
                } elseif (str_starts_with($line, 'DTEND;VALUE=DATE:') || str_starts_with($line, 'DTEND:')) {
                    $parts = explode(':', $line);
                    $curEnd = trim($parts[1] ?? '');
                }
            }
        }

        return $events;
    }

    /**
     * Kamus Lengkap Hari Libur Nasional & Keagamaan Indonesia
     * Mendukung tahun 2025, 2026, 2027 serta otomatis menjamin hari libur nasional tetap untuk tahun berikutnya (2028+)
     */
    protected function getIndonesianHolidayDictionary(int $yearStart, int $yearEnd): array
    {
        $all = [
            // 2025
            ['date' => '2025-01-01', 'name' => 'Tahun Baru 2025 Masehi', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-01-27', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-01-29', 'name' => 'Tahun Baru Imlek 2576 Kongzili', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-03-29', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1947', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-03-31', 'name' => 'Hari Raya Idul Fitri 1446 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-04-01', 'name' => 'Hari Raya Idul Fitri 1446 H (Hari Ke-2)', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-04-18', 'name' => 'Wafat Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-05-01', 'name' => 'Hari Buruh Internasional', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak 2569 BE', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-05-29', 'name' => 'Kenaikan Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-06-01', 'name' => 'Hari Lahir Pancasila', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-06-07', 'name' => 'Hari Raya Idul Adha 1446 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 Hijriah', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-08-17', 'name' => 'Hari Kemerdekaan RI ke-80', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2025-12-25', 'name' => 'Hari Raya Natal', 'is_libur' => true, 'warna' => 'red'],

            // 2026
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026 Masehi', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-01-16', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek 2577 Kongzili', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-03-20', 'name' => 'Hari Raya Idul Fitri 1447 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-03-21', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari Ke-2)', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-03-24', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1948', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-04-03', 'name' => 'Wafat Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-05-27', 'name' => 'Hari Raya Idul Adha 1447 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-05-31', 'name' => 'Hari Raya Waisak 2570 BE', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-06-16', 'name' => 'Tahun Baru Islam 1448 Hijriah', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan RI ke-81', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-08-25', 'name' => 'Maulid Nabi Muhammad SAW 1448 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'is_libur' => true, 'warna' => 'red'],

            // 2027
            ['date' => '2027-01-01', 'name' => 'Tahun Baru 2027 Masehi', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-01-05', 'name' => 'Isra Mi\'raj Nabi Muhammad SAW', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-02-06', 'name' => 'Tahun Baru Imlek 2578 Kongzili', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-03-09', 'name' => 'Hari Raya Idul Fitri 1448 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-03-10', 'name' => 'Hari Raya Idul Fitri 1448 H (Hari Ke-2)', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-03-12', 'name' => 'Hari Suci Nyepi Tahun Baru Saka 1949', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-03-26', 'name' => 'Wafat Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-05-01', 'name' => 'Hari Buruh Internasional', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-05-06', 'name' => 'Kenaikan Isa Almasih', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-05-16', 'name' => 'Hari Raya Idul Adha 1448 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-05-20', 'name' => 'Hari Raya Waisak 2571 BE', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-06-01', 'name' => 'Hari Lahir Pancasila', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-06-06', 'name' => 'Tahun Baru Islam 1449 Hijriah', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-08-17', 'name' => 'Hari Kemerdekaan RI ke-82', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-08-14', 'name' => 'Maulid Nabi Muhammad SAW 1449 H', 'is_libur' => true, 'warna' => 'red'],
            ['date' => '2027-12-25', 'name' => 'Hari Raya Natal', 'is_libur' => true, 'warna' => 'red'],
        ];

        // Untuk tahun yang belum ada di kamus statis (misal 2028 ke atas), generate hari libur nasional tetap
        for ($y = max(2028, $yearStart); $y <= $yearEnd; $y++) {
            $all[] = ['date' => "{$y}-01-01", 'name' => "Tahun Baru {$y} Masehi", 'is_libur' => true, 'warna' => 'red'];
            $all[] = ['date' => "{$y}-05-01", 'name' => 'Hari Buruh Internasional', 'is_libur' => true, 'warna' => 'red'];
            $all[] = ['date' => "{$y}-06-01", 'name' => 'Hari Lahir Pancasila', 'is_libur' => true, 'warna' => 'red'];
            $all[] = ['date' => "{$y}-08-17", 'name' => "Hari Kemerdekaan RI ke-" . ($y - 1945), 'is_libur' => true, 'warna' => 'red'];
            $all[] = ['date' => "{$y}-12-25", 'name' => 'Hari Raya Natal', 'is_libur' => true, 'warna' => 'red'];
        }

        return array_filter($all, function ($item) use ($yearStart, $yearEnd) {
            $y = (int) substr($item['date'], 0, 4);
            return $y >= $yearStart && $y <= $yearEnd;
        });
    }
}
