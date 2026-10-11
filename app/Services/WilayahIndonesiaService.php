<?php

namespace App\Services;

class WilayahIndonesiaService
{
    /**
     * Daftar Semua Provinsi di Indonesia (Jawa Barat di paling atas)
     */
    public static function getProvinsi(): array
    {
        return [
            'Jawa Barat',
            'DKI Jakarta',
            'Banten',
            'Jawa Tengah',
            'DI Yogyakarta',
            'Jawa Timur',
            'Bali',
            'Lampung',
            'Sumatera Selatan',
            'Sumatera Barat',
            'Sumatera Utara',
            'Riau',
            'Kepulauan Riau',
            'Jambi',
            'Bengkulu',
            'Bangka Belitung',
            'Aceh',
            'Kalimantan Barat',
            'Kalimantan Tengah',
            'Kalimantan Selatan',
            'Kalimantan Timur',
            'Kalimantan Utara',
            'Sulawesi Selatan',
            'Sulawesi Utara',
            'Sulawesi Tengah',
            'Sulawesi Tenggara',
            'Sulawesi Barat',
            'Gorontalo',
            'Nusa Tenggara Barat',
            'Nusa Tenggara Timur',
            'Maluku',
            'Maluku Utara',
            'Papua',
            'Papua Barat',
            'Papua Barat Daya',
            'Papua Selatan',
            'Papua Tengah',
            'Papua Pegunungan',
        ];
    }

    /**
     * Daftar Kabupaten / Kota per Provinsi
     */
    public static function getKabupatenPerProvinsi(): array
    {
        return [
            'Jawa Barat' => [
                'Kabupaten Cirebon',
                'Kota Cirebon',
                'Kabupaten Indramayu',
                'Kabupaten Majalengka',
                'Kabupaten Kuningan',
                'Kabupaten Bandung',
                'Kota Bandung',
                'Kabupaten Bandung Barat',
                'Kota Cimahi',
                'Kabupaten Bekasi',
                'Kota Bekasi',
                'Kabupaten Bogor',
                'Kota Bogor',
                'Kota Depok',
                'Kabupaten Karawang',
                'Kabupaten Purwakarta',
                'Kabupaten Subang',
                'Kabupaten Sumedang',
                'Kabupaten Sukabumi',
                'Kota Sukabumi',
                'Kabupaten Cianjur',
                'Kabupaten Garut',
                'Kabupaten Tasikmalaya',
                'Kota Tasikmalaya',
                'Kabupaten Ciamis',
                'Kota Banjar',
                'Kabupaten Pangandaran',
            ],
            'DKI Jakarta' => [
                'Kota Jakarta Pusat',
                'Kota Jakarta Selatan',
                'Kota Jakarta Timur',
                'Kota Jakarta Barat',
                'Kota Jakarta Utara',
                'Kabupaten Kepulauan Seribu',
            ],
            'Banten' => [
                'Kota Tangerang',
                'Kota Tangerang Selatan',
                'Kabupaten Tangerang',
                'Kota Serang',
                'Kabupaten Serang',
                'Kota Cilegon',
                'Kabupaten Lebak',
                'Kabupaten Pandeglang',
            ],
            'Jawa Tengah' => [
                'Kabupaten Brebes',
                'Kabupaten Tegal',
                'Kota Tegal',
                'Kabupaten Pemalang',
                'Kabupaten Pekalongan',
                'Kota Pekalongan',
                'Kabupaten Banyumas',
                'Kabupaten Cilacap',
                'Kabupaten Purbalingga',
                'Kabupaten Banjarnegara',
                'Kabupaten Kebumen',
                'Kabupaten Magelang',
                'Kota Magelang',
                'Kota Semarang',
                'Kabupaten Semarang',
                'Kota Surakarta (Solo)',
                'Kabupaten Klaten',
                'Kabupaten Sukoharjo',
                'Kabupaten Karanganyar',
                'Kabupaten Wonogiri',
                'Kabupaten Sragen',
                'Kabupaten Boyolali',
                'Kabupaten Kudus',
                'Kabupaten Pati',
                'Kabupaten Jepara',
                'Kabupaten Rembang',
                'Kabupaten Blora',
                'Kabupaten Grobogan',
                'Kabupaten Demak',
                'Kabupaten Kendal',
                'Kabupaten Batang',
                'Kabupaten Temanggung',
                'Kabupaten Wonosobo',
                'Kota Salatiga',
            ],
            'DI Yogyakarta' => [
                'Kota Yogyakarta',
                'Kabupaten Sleman',
                'Kabupaten Bantul',
                'Kabupaten Kulon Progo',
                'Kabupaten Gunungkidul',
            ],
            'Jawa Timur' => [
                'Kota Surabaya',
                'Kabupaten Sidoarjo',
                'Kabupaten Gresik',
                'Kota Malang',
                'Kabupaten Malang',
                'Kota Batu',
                'Kabupaten Mojokerto',
                'Kota Mojokerto',
                'Kabupaten Pasuruan',
                'Kota Pasuruan',
                'Kabupaten Probolinggo',
                'Kota Probolinggo',
                'Kabupaten Banyuwangi',
                'Kabupaten Jember',
                'Kabupaten Kediri',
                'Kota Kediri',
                'Kabupaten Blitar',
                'Kota Blitar',
                'Kabupaten Madiun',
                'Kota Madiun',
                'Kabupaten Bojonegoro',
                'Kabupaten Tuban',
                'Kabupaten Lamongan',
                'Kabupaten Ngawi',
                'Kabupaten Magetan',
                'Kabupaten Ponorogo',
                'Kabupaten Pacitan',
                'Kabupaten Trenggalek',
                'Kabupaten Tulungagung',
                'Kabupaten Nganjuk',
                'Kabupaten Jombang',
                'Kabupaten Lumajang',
                'Kabupaten Bondowoso',
                'Kabupaten Situbondo',
                'Kabupaten Bangkalan',
                'Kabupaten Sampang',
                'Kabupaten Pamekasan',
                'Kabupaten Sumenep',
            ],
            'Bali' => [
                'Kota Denpasar',
                'Kabupaten Badung',
                'Kabupaten Gianyar',
                'Kabupaten Tabanan',
                'Kabupaten Buleleng',
                'Kabupaten Karangasem',
                'Kabupaten Klungkung',
                'Kabupaten Bangli',
                'Kabupaten Jembrana',
            ],
            'Lampung' => [
                'Kota Bandar Lampung',
                'Kota Metro',
                'Kabupaten Lampung Selatan',
                'Kabupaten Lampung Tengah',
                'Kabupaten Lampung Timur',
                'Kabupaten Lampung Utara',
                'Kabupaten Pringsewu',
                'Kabupaten Pesawaran',
                'Kabupaten Tanggamus',
            ],
            'Sumatera Selatan' => [
                'Kota Palembang',
                'Kota Prabumulih',
                'Kabupaten Ogan Komering Ilir',
                'Kabupaten Ogan Ilir',
                'Kabupaten Banyuasin',
                'Kabupaten Muara Enim',
                'Kabupaten Lahat',
            ],
            'Sumatera Barat' => [
                'Kota Padang',
                'Kota Bukittinggi',
                'Kabupaten Agam',
                'Kabupaten Tanah Datar',
                'Kabupaten Padang Pariaman',
            ],
            'Sumatera Utara' => [
                'Kota Medan',
                'Kota Binjai',
                'Kabupaten Deli Serdang',
                'Kabupaten Langkat',
                'Kota Pematangsiantar',
            ],
        ];
    }

    /**
     * Kecamatan untuk Kabupaten di Jawa Barat (Sangat detail untuk Cirebon & Ciayumajakuning)
     */
    public static function getKecamatanPerKabupaten(): array
    {
        return [
            'Kabupaten Cirebon' => [
                'Arjawinangun',
                'Ciwaringin',
                'Klangenan',
                'Palimanan',
                'Gempol',
                'Susukan',
                'Panguragan',
                'Gegesik',
                'Kaliwedi',
                'Kapetakan',
                'Suranenggala',
                'Plered',
                'Weru',
                'Kedawung',
                'Gunungjati',
                'Tengahtani',
                'Sumber',
                'Talun',
                'Dukupuntang',
                'Plumbon',
                'Depok',
                'Jamblang',
                'Pabuaran',
                'Babakan',
                'Ciledug',
                'Waled',
                'Pasaleman',
                'Losari',
                'Gebang',
                'Astanajapura',
                'Mundu',
                'Beber',
                'Greged',
                'Sedong',
                'Sindanglaut / Lemahabang',
                'Karangsembung',
                'Karangwareng',
            ],
            'Kota Cirebon' => [
                'Kejaksan',
                'Kesambi',
                'Harjamukti',
                'Pekalipan',
                'Lemahwungkuk',
            ],
            'Kabupaten Indramayu' => [
                'Jatibarang',
                'Kertasmaya',
                'Krangkeng',
                'Karangampel',
                'Juntinyuat',
                'Sliyeg',
                'Kedokanbunder',
                'Widasari',
                'Bangodua',
                'Tukdana',
                'Lohbener',
                'Sindang',
                'Indramayu',
                'Balongan',
                'Cantigi',
                'Pasekan',
                'Arahan',
                'Losarang',
                'Kandanghaur',
                'Gabuswetan',
                'Cikedung',
                'Terisi',
                'Kroya',
                'Gantar',
                'Haurgeulis',
                'Anjatan',
                'Sukra',
                'Patrol',
                'Bongas',
            ],
            'Kabupaten Majalengka' => [
                'Jatitujuh',
                'Kertajati',
                'Ligung',
                'Sumberjaya',
                'Dawuan',
                'Jatiwangi',
                'Palasah',
                'Leuwimunding',
                'Sindangwangi',
                'Rajagaluh',
                'Sukahaji',
                'Majalengka',
                'Cigasong',
                'Kasokandel',
                'Kadipaten',
                'Panyingkiran',
                'Maja',
                'Argapura',
                'Banjaran',
                'Talaga',
                'Cikijing',
                'Cingambul',
                'Bantarujeg',
                'Lemahsugih',
                'Malausma',
            ],
            'Kabupaten Kuningan' => [
                'Cilimus',
                'Mandirancan',
                'Pasawahan',
                'Pancalang',
                'Kramatmulya',
                'Jalaksana',
                'Japara',
                'Cigandamekar',
                'Kuningan',
                'Sindangagung',
                'Garawangi',
                'Luragung',
                'Ciawigebang',
                'Cidahu',
                'Kalimanggis',
                'Lebakwangi',
                'Maleber',
                'Ciniru',
                'Hantara',
                'Darma',
                'Kadugede',
                'Nusaherang',
                'Subang',
                'Selajambe',
                'Cilebak',
            ],
            'Kabupaten Bandung' => [
                'Soreang', 'Banjaran', 'Baleendah', 'Dayeuhkolot', 'Bojongsoang', 'Ciparay', 'Majalaya', 'Rancaekek', 'Cicalengka', 'Cileunyi', 'Margahayu', 'Katapang'
            ],
            'Kota Bandung' => [
                'Coblong', 'Sukajadi', 'Cicendo', 'Andir', 'Sumur Bandung', 'Regol', 'Lengkong', 'Batununggal', 'Kiaracondong', 'Arcamanik', 'Antapani', 'Ujungberung', 'Buahbatu', 'Bandung Kidul'
            ],
            'Kabupaten Bekasi' => [
                'Cikarang Pusat', 'Cikarang Utara', 'Cikarang Selatan', 'Cikarang Barat', 'Cikarang Timur', 'Tambun Selatan', 'Tambun Utara', 'Cibitung', 'Setu', 'Serang Baru', 'Babelan', 'Tarumajaya'
            ],
            'Kota Bekasi' => [
                'Bekasi Timur', 'Bekasi Barat', 'Bekasi Selatan', 'Bekasi Utara', 'Rawa Lumbu', 'Pondok Gede', 'Jatiasih', 'Medan Satria'
            ],
            'Kabupaten Subang' => [
                'Subang', 'Pagaden', 'Pagaden Barat', 'Pabuaran', 'Ciasem', 'Patokbeusi', 'Kalijati', 'Purwadadi', 'Cipendeuy', 'Pamanukan', 'Pusakajaya'
            ],
            'Kabupaten Karawang' => [
                'Karawang Barat', 'Karawang Timur', 'Telukjambe Timur', 'Telukjambe Barat', 'Klari', 'Cikampek', 'Jatisari', 'Kotabaru', 'Rengasdengklok'
            ],
        ];
    }

    /**
     * Desa / Kelurahan per Kecamatan di Cirebon & Sekitarnya
     */
    public static function getDesaPerKecamatan(): array
    {
        return [
            // Cirebon - Arjawinangun
            'Arjawinangun' => [
                'Tegalgubug',
                'Tegalgubug Lor',
                'Jungjang',
                'Jungjang Wetan',
                'Arjawinangun',
                'Karangsambung',
                'Rawagatsu',
                'Sende',
                'Geyongan',
                'Bulak',
                'Karanganyar',
            ],
            // Cirebon - Ciwaringin
            'Ciwaringin' => [
                'Babakan',
                'Babakan Ciwaringin',
                'Bringin',
                'Budur',
                'Ciwaringin',
                'Galagamba',
                'Gintungranjeng',
            ],
            // Cirebon - Klangenan
            'Klangenan' => [
                'Danawarih',
                'Jemaras Kidul',
                'Jemaras Lor',
                'Klangenan',
                'Kreyo',
                'Serang',
                'Slangit',
                'Bangkaloa Ilir',
            ],
            // Cirebon - Palimanan
            'Palimanan' => [
                'Balerante',
                'Beberan',
                'Cengkuang',
                'Ciawi',
                'Cilukrak',
                'Kepuh',
                'Lungbenda',
                'Palimanan Barat',
                'Panongan',
                'Pegagan',
                'Semplo',
                'Tegalkarang',
            ],
            // Cirebon - Gempol
            'Gempol' => [
                'Cikeusal',
                'Cupang',
                'Gempol',
                'Kedungbunder',
                'Palimanan Timur',
                'Walahar',
            ],
            // Cirebon - Susukan
            'Susukan' => [
                'Bunder',
                'Gintunglor',
                'Jatian',
                'Kedondong',
                'Kejiwan',
                'Luwungkencana',
                'Mojopuro',
                'Susukan',
                'Tangkil',
                'Ujunggebang',
                'Wiyong',
                'Bojong Kulon',
            ],
            // Cirebon - Panguragan
            'Panguragan' => [
                'Alang-alang',
                'Gujeg',
                'Kalianyar',
                'Karanganyar',
                'Kroya',
                'Panguragan',
                'Panguragan Kulon',
                'Panguragan Lor',
                'Panguragan Wetan',
                'Lempuyang',
            ],
            // Cirebon - Gegesik
            'Gegesik' => [
                'Bayalangu Kidul',
                'Bayalangu Lor',
                'Gegesik Kidul',
                'Gegesik Kulon',
                'Gegesik Lor',
                'Gegesik Wetan',
                'Jagapura Kidul',
                'Jagapura Kulon',
                'Jagapura Lor',
                'Jagapura Wetan',
                'Kedungdalem',
                'Panunggul',
                'Sibubut',
                'Slendra',
            ],
            // Cirebon - Kaliwedi
            'Kaliwedi' => [
                'Guwa Kidul',
                'Guwa Lor',
                'Kalideres',
                'Kaliwedi Kidul',
                'Kaliwedi Lor',
                'Prajawinangun Kulon',
                'Prajawinangun Wetan',
                'Ujungsemi',
                'Wargabinangun',
            ],
            // Cirebon - Kapetakan
            'Kapetakan' => [
                'Bungko',
                'Bungko Lor',
                'Dukuh',
                'Grogol',
                'Kapetakan',
                'Karangkendal',
                'Kertasureh',
                'Pegagan Kidul',
                'Pegagan Lor',
            ],
            // Cirebon - Plered
            'Plered' => [
                'Cangkring',
                'Gamel',
                'Kaliwulu',
                'Panembahan',
                'Pangkalan',
                'Sarabau',
                'Tegalsari',
                'Trusmi Kulon',
                'Trusmi Wetan',
                'Wotgali',
            ],
            // Cirebon - Weru
            'Weru' => [
                'Karangsari',
                'Kertawinangun',
                'Megu Cilik',
                'Megu Gede',
                'Setu Kulon',
                'Setu Wetan',
                'Tegalwangi',
                'Weru Kidul',
                'Weru Lor',
            ],
            // Cirebon - Sumber
            'Sumber' => [
                'Babakan',
                'Gegunung',
                'Kemantren',
                'Kenanga',
                'Matangaji',
                'Pasalakan',
                'Pejambon',
                'Perbutulan',
                'Sendang',
                'Sidawangi',
                'Sumber',
                'Tukmudal',
                'Watubelah',
            ],
            // Cirebon - Kedawung
            'Kedawung' => [
                'Kedawung',
                'Kedungdawa',
                'Kedungjaya',
                'Kertawinangun',
                'Pilangsari',
                'Sutawinangun',
                'Tuk',
            ],
            // Indramayu - Jatibarang
            'Jatibarang' => [
                'Jatibarang', 'Jatibarang Baru', 'Bulak', 'Bulak Lor', 'Pilangsari', 'Krasak', 'Kebulen', 'Pawidean', 'Kalimati', 'Jatisawit'
            ],
            // Indramayu - Kertasmaya
            'Kertasmaya' => [
                'Kertasmaya', 'Lemahayu', 'Tenajar', 'Tenajar Kidul', 'Tenajar Lor', 'Tegalwirangrong', 'Keliwon', 'Janggleng'
            ],
            // Majalengka - Jatiwangi
            'Jatiwangi' => [
                'Jatiwangi', 'Sutawangi', 'Ciborelang', 'Cicadas', 'Burujul Kulon', 'Burujul Wetan', 'Andir', 'Jatisura', 'Pinangraja'
            ],
        ];
    }

    /**
     * Koordinat Wilayah untuk Visualisasi Peta Sebaran Domisili
     */
    public static function getCoordinates(string $kabupaten, ?string $kecamatan = null, ?string $desa = null): array
    {
        // Koordinat Desa Khusus
        $desaCoords = [
            'Tegalgubug'     => ['lat' => -6.6521, 'lng' => 108.4125],
            'Tegalgubug Lor' => ['lat' => -6.6450, 'lng' => 108.4180],
            'Jungjang'       => ['lat' => -6.6430, 'lng' => 108.4050],
            'Arjawinangun'   => ['lat' => -6.6480, 'lng' => 108.4100],
            'Babakan'        => ['lat' => -6.6900, 'lng' => 108.3800],
            'Ciwaringin'     => ['lat' => -6.6940, 'lng' => 108.3750],
            'Wiyong'         => ['lat' => -6.6150, 'lng' => 108.3900],
            'Bojong Kulon'   => ['lat' => -6.6200, 'lng' => 108.4000],
            'Trusmi Wetan'   => ['lat' => -6.7100, 'lng' => 108.5050],
            'Sumber'         => ['lat' => -6.7611, 'lng' => 108.4794],
        ];

        $cleanDesa = $desa ? trim(preg_replace('/^(desa|kel\.|kelurahan)\s+/i', '', $desa)) : null;
        if ($cleanDesa && isset($desaCoords[$cleanDesa])) {
            return $desaCoords[$cleanDesa];
        }

        // Koordinat Kecamatan Khusus
        $kecCoords = [
            'Arjawinangun' => ['lat' => -6.6465, 'lng' => 108.4110],
            'Ciwaringin'   => ['lat' => -6.6920, 'lng' => 108.3780],
            'Klangenan'    => ['lat' => -6.7050, 'lng' => 108.4500],
            'Palimanan'    => ['lat' => -6.7070, 'lng' => 108.4230],
            'Gempol'       => ['lat' => -6.7150, 'lng' => 108.3950],
            'Susukan'      => ['lat' => -6.6180, 'lng' => 108.3920],
            'Panguragan'   => ['lat' => -6.6350, 'lng' => 108.4550],
            'Gegesik'      => ['lat' => -6.5850, 'lng' => 108.4600],
            'Kaliwedi'     => ['lat' => -6.5950, 'lng' => 108.4150],
            'Plered'       => ['lat' => -6.7080, 'lng' => 108.5080],
            'Weru'         => ['lat' => -6.7150, 'lng' => 108.5150],
            'Kedawung'     => ['lat' => -6.7280, 'lng' => 108.5350],
            'Gunungjati'   => ['lat' => -6.6850, 'lng' => 108.5450],
            'Sumber'       => ['lat' => -6.7611, 'lng' => 108.4794],
            'Talun'        => ['lat' => -6.7550, 'lng' => 108.5150],
            'Jatibarang'   => ['lat' => -6.4780, 'lng' => 108.3050],
            'Kertasemaya'  => ['lat' => -6.5350, 'lng' => 108.3450],
            'Kertasmaya'   => ['lat' => -6.5350, 'lng' => 108.3450],
            'Jatiwangi'    => ['lat' => -6.7450, 'lng' => 108.2600],
            'Kertajati'    => ['lat' => -6.6500, 'lng' => 108.1900],
            'Cilimus'      => ['lat' => -6.8850, 'lng' => 108.4950],
            'Banyusari'    => ['lat' => -6.2800, 'lng' => 107.5400],
            'Muaragembong' => ['lat' => -6.0020, 'lng' => 107.0300],
        ];

        $cleanKec = $kecamatan ? trim(preg_replace('/^(kec\.|kecamatan)\s+/i', '', $kecamatan)) : null;
        if ($cleanKec && isset($kecCoords[$cleanKec])) {
            return $kecCoords[$cleanKec];
        }

        // Koordinat Kabupaten
        $kabCoords = [
            'Kabupaten Cirebon'     => ['lat' => -6.7611, 'lng' => 108.4794],
            'Kota Cirebon'          => ['lat' => -6.7320, 'lng' => 108.5523],
            'Kabupaten Indramayu'   => ['lat' => -6.3264, 'lng' => 108.3200],
            'Kabupaten Majalengka'  => ['lat' => -6.8361, 'lng' => 108.2275],
            'Kabupaten Kuningan'    => ['lat' => -6.9765, 'lng' => 108.4831],
            'Kabupaten Bandung'     => ['lat' => -7.0253, 'lng' => 107.5198],
            'Kota Bandung'          => ['lat' => -6.9175, 'lng' => 107.6191],
            'Kabupaten Bandung Barat'=> ['lat' => -6.8441, 'lng' => 107.5028],
            'Kota Cimahi'           => ['lat' => -6.8723, 'lng' => 107.5420],
            'Kabupaten Subang'      => ['lat' => -6.5593, 'lng' => 107.7601],
            'Kabupaten Sumedang'    => ['lat' => -6.8586, 'lng' => 107.9266],
            'Kabupaten Karawang'    => ['lat' => -6.3073, 'lng' => 107.3075],
            'Kabupaten Purwakarta'  => ['lat' => -6.5569, 'lng' => 107.4433],
            'Kabupaten Bekasi'      => ['lat' => -6.2415, 'lng' => 107.0988],
            'Kota Bekasi'           => ['lat' => -6.2383, 'lng' => 106.9756],
            'Kabupaten Bogor'       => ['lat' => -6.5518, 'lng' => 106.6291],
            'Kota Bogor'            => ['lat' => -6.5971, 'lng' => 106.8060],
            'Kota Depok'            => ['lat' => -6.4025, 'lng' => 106.7942],
            'Kabupaten Sukabumi'    => ['lat' => -6.9277, 'lng' => 106.9299],
            'Kota Sukabumi'         => ['lat' => -6.9277, 'lng' => 106.9299],
            'Kabupaten Cianjur'     => ['lat' => -6.8174, 'lng' => 107.1425],
            'Kabupaten Garut'       => ['lat' => -7.2278, 'lng' => 107.9086],
            'Kabupaten Tasikmalaya' => ['lat' => -7.3547, 'lng' => 108.1132],
            'Kota Tasikmalaya'      => ['lat' => -7.3274, 'lng' => 108.2207],
            'Kabupaten Ciamis'      => ['lat' => -7.3260, 'lng' => 108.3533],
            'Kota Banjar'           => ['lat' => -7.3686, 'lng' => 108.5332],
            'Kabupaten Pangandaran' => ['lat' => -7.6823, 'lng' => 108.6504],
            // Luar Jabar Populer
            'Kabupaten Brebes'      => ['lat' => -6.8703, 'lng' => 108.9760],
            'Kota Tegal'            => ['lat' => -6.8694, 'lng' => 109.1402],
            'Kota Jakarta Timur'    => ['lat' => -6.2250, 'lng' => 106.9004],
            'Kota Jakarta Selatan'  => ['lat' => -6.2615, 'lng' => 106.8106],
            'Kota Tangerang'        => ['lat' => -6.1783, 'lng' => 106.6319],
        ];

        // Normalisasi nama kabupaten untuk pencocokan toleran
        $norm = self::normalizeKabupatenName($kabupaten);
        if (isset($kabCoords[$norm])) {
            return $kabCoords[$norm];
        }

        // Default Cirebon (Pusat Sekolah SMK Plus Al Hilal)
        return ['lat' => -6.6465, 'lng' => 108.4110];
    }

    /**
     * Normalisasi nama kabupaten (misal "Cirebon" atau "Kab. Cirebon" -> "Kabupaten Cirebon")
     */
    public static function normalizeKabupatenName(?string $name): string
    {
        if (empty($name)) return '';
        $name = trim($name);
        
        $map = [
            'cirebon'        => 'Kabupaten Cirebon',
            'kab. cirebon'   => 'Kabupaten Cirebon',
            'kabupaten cirebon' => 'Kabupaten Cirebon',
            'kota cirebon'   => 'Kota Cirebon',
            'indramayu'      => 'Kabupaten Indramayu',
            'kab. indramayu' => 'Kabupaten Indramayu',
            'majalengka'     => 'Kabupaten Majalengka',
            'kab. majalengka'=> 'Kabupaten Majalengka',
            'kuningan'       => 'Kabupaten Kuningan',
            'kab. kuningan'  => 'Kabupaten Kuningan',
            'bekasi'         => 'Kabupaten Bekasi',
            'kab. bekasi'    => 'Kabupaten Bekasi',
            'kota bekasi'    => 'Kota Bekasi',
            'karawang'       => 'Kabupaten Karawang',
            'kab. karawang'  => 'Kabupaten Karawang',
            'subang'         => 'Kabupaten Subang',
            'kab. subang'    => 'Kabupaten Subang',
            'purwakarta'     => 'Kabupaten Purwakarta',
            'kab. purwakarta'=> 'Kabupaten Purwakarta',
            'sukabumi'       => 'Kabupaten Sukabumi',
            'kab. sukabumi'  => 'Kabupaten Sukabumi',
            'brebes'         => 'Kabupaten Brebes',
            'kab. brebes'    => 'Kabupaten Brebes',
            'jakarta timur'  => 'Kota Jakarta Timur',
            'kota jakarta timur' => 'Kota Jakarta Timur',
        ];

        $lower = strtolower($name);
        return $map[$lower] ?? $name;
    }
}
