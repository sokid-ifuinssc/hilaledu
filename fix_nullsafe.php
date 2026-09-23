<?php
$dir = new RecursiveDirectoryIterator('d:\Garapan\HilalEdu\resources\views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

$replacements = [
    '/->siswa->kelas->/' => '->siswa?->kelas?->',
    '/->kelas->nama/' => '->kelas?->nama',
    '/->kelas->nama_lengkap/' => '->kelas?->nama_lengkap',
    '/->kelas->nama_kelas/' => '->kelas?->nama_kelas',
    '/->kelas->jurusan->/' => '->kelas?->jurusan?->',
    '/->jurusan->nama/' => '->jurusan?->nama',
    '/->guru->name/' => '->guru?->name',
    '/->guru->nama_lengkap/' => '->guru?->nama_lengkap',
];

$count = 0;
foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $newContent = $content;
    foreach ($replacements as $pattern => $replacement) {
        $newContent = preg_replace($pattern, $replacement, $newContent);
    }
    if ($newContent !== $content) {
        file_put_contents($path, $newContent);
        $count++;
    }
}
echo "Updated $count files.\n";
