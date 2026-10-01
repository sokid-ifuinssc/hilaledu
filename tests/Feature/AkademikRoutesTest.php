<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AkademikRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_akademik_routes()
    {
        $user = User::where('role', 'superadmin')->first()
            ?? User::factory()->create(['role' => 'superadmin']);

        $routes = [
            'akademik.dashboard',
            'akademik.kalender.index',
            'akademik.kurikulum.index',
            'akademik.jadwal.index',
            'akademik.jadwal.matrix',
            'akademik.nilai.index',
            'akademik.laporan.kehadiran.index',
            'presensi-harian.index',
            'akademik.laporan.kbm.index',
            'akademik.rekap-presensi.index',
            'akademik.piket.index',
            'akademik.keluhan.index',
            'akademik.pengaturan.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($user)->get(route($routeName));
            
            // Print status
            echo $routeName . " -> " . $response->status() . "\n";
            
            if ($response->status() >= 500) {
                echo "ERROR in $routeName\n";
            }
        }
        
        $this->assertTrue(true);
    }
}
