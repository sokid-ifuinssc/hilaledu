<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = [
            'akademik.jadwal.index',
            'akademik.nilai.index',
            'prakerin.industri.index',
            'prakerin.jurnal.index',
            'keuangan.tagihan.index',
            'keuangan.pembayaran.index',
            'koperasi.anggota.index',
            'koperasi.transaksi.index',
            'bk.pelanggaran.index',
            'bk.poin.index',
            'tracer.alumni.index',
            'tracer.kuesioner.index'
        ];
        
        $admin = \App\Models\User::where('role', 'superadmin')->first();
        if (!$admin) {
            $this->error("Superadmin not found");
            return;
        }

        foreach ($routes as $route) {
            try {
                $url = route($route);
                $this->info("Testing $route ($url)");
                
                $request = \Illuminate\Http\Request::create($url, 'GET');
                auth()->login($admin);
                
                $response = app()->handle($request);
                
                if ($response->getStatusCode() === 200 || $response->getStatusCode() === 302) {
                    $this->info("SUCCESS: $route returns " . $response->getStatusCode());
                } else {
                    $this->error("FAIL: $route returns " . $response->getStatusCode());
                }
            } catch (\Exception $e) {
                $this->error("EXCEPTION: $route -> " . $e->getMessage());
            }
        }
    }
}
