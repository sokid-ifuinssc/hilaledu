<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginLogService
{
    /**
     * Catat Login Berhasil
     */
    public function logSuccess(User $user, ?Request $request = null): ?LoginLog
    {
        $request = $request ?: request();
        if ($request && $request->attributes->get('login_logged_success')) {
            return null;
        }
        if ($request) {
            $request->attributes->set('login_logged_success', true);
        }

        $ip = $this->getClientIp($request);
        $userAgent = $request->userAgent() ?? '';
        $device = $this->parseUserAgent($userAgent);
        $geo = $this->getIpLocation($ip);

        return LoginLog::create([
            'user_id'       => $user->id,
            'login_input'   => $user->email ?? $user->username,
            'user_name'     => $user->name ?? $user->nama_lengkap ?? $user->username,
            'user_role'     => $user->role ?? 'siswa',
            'ip_address'    => $ip,
            'status'        => 'success',
            'failure_reason'=> null,
            'user_agent'    => $userAgent,
            'device_type'   => $device['device_type'],
            'browser'       => $device['browser'],
            'platform'      => $device['platform'],
            'country'       => $geo['country'],
            'country_code'  => $geo['country_code'],
            'region'        => $geo['region'],
            'city'          => $geo['city'],
            'latitude'      => $geo['latitude'],
            'longitude'     => $geo['longitude'],
            'timezone'      => $geo['timezone'],
            'isp'           => $geo['isp'],
            'is_suspicious' => false,
            'login_at'      => now(),
        ]);
    }

    /**
     * Catat Login Gagal
     */
    public function logFailed(string $loginInput, ?string $reason = null, ?Request $request = null, ?User $user = null): ?LoginLog
    {
        $request = $request ?: request();
        if ($request && $request->attributes->get('login_logged_failed')) {
            return null;
        }
        if ($request) {
            $request->attributes->set('login_logged_failed', true);
        }

        $ip = $this->getClientIp($request);
        $userAgent = $request->userAgent() ?? '';
        $device = $this->parseUserAgent($userAgent);
        $geo = $this->getIpLocation($ip);

        // Periksa apakah ada lebih dari 3 kegagalan dalam 15 menit terakhir dari IP atau login yang sama
        $recentFails = LoginLog::where('status', 'failed')
            ->where(function ($q) use ($ip, $loginInput) {
                $q->where('ip_address', $ip)
                  ->orWhere('login_input', $loginInput);
            })
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        $isSuspicious = ($recentFails >= 2); // Dengan kegagalan ini jadi >= 3

        return LoginLog::create([
            'user_id'       => $user?->id,
            'login_input'   => $loginInput,
            'user_name'     => $user?->name ?? $user?->nama_lengkap ?? null,
            'user_role'     => $user?->role ?? null,
            'ip_address'    => $ip,
            'status'        => 'failed',
            'failure_reason'=> $reason ?? 'Kredensial tidak valid',
            'user_agent'    => $userAgent,
            'device_type'   => $device['device_type'],
            'browser'       => $device['browser'],
            'platform'      => $device['platform'],
            'country'       => $geo['country'],
            'country_code'  => $geo['country_code'],
            'region'        => $geo['region'],
            'city'          => $geo['city'],
            'latitude'      => $geo['latitude'],
            'longitude'     => $geo['longitude'],
            'timezone'      => $geo['timezone'],
            'isp'           => $geo['isp'],
            'is_suspicious' => $isSuspicious,
            'login_at'      => now(),
        ]);
    }

    /**
     * Ambil Real Client IP (memperhatikan reverse proxy / cloudflare jika ada)
     */
    public function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
        ];

        foreach ($headers as $header) {
            $value = $request->server($header);
            if (!empty($value)) {
                $ips = explode(',', $value);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?: '127.0.0.1';
    }

    /**
     * Parse User Agent untuk Browser, OS Platform, dan Device Type
     */
    public function parseUserAgent(?string $ua): array
    {
        if (empty($ua)) {
            return [
                'browser'     => 'Unknown',
                'platform'    => 'Unknown',
                'device_type' => 'Desktop',
            ];
        }

        // Platform / OS
        $platform = 'Unknown OS';
        if (preg_match('/windows nt 10\.0/i', $ua)) {
            $platform = 'Windows 10/11';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $platform = 'Windows 8.1';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $platform = 'Windows 7';
        } elseif (preg_match('/windows/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/android (\d+(\.\d+)?)/i', $ua, $matches)) {
            $platform = 'Android ' . ($matches[1] ?? '');
        } elseif (preg_match('/iphone os (\d+([._]\d+)?)/i', $ua, $matches)) {
            $platform = 'iOS ' . str_replace('_', '.', $matches[1] ?? '');
        } elseif (preg_match('/ipad/i', $ua)) {
            $platform = 'iPadOS';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        }

        // Device Type
        $deviceType = 'Desktop';
        if (preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $ua)) {
            $deviceType = 'Tablet';
        } elseif (preg_match('/(mobile|iphone|ipod|blackberry|opera mini|iemobile)/i', $ua)) {
            $deviceType = 'Mobile';
        }

        // Browser
        $browser = 'Unknown Browser';
        if (preg_match('/edg\/(\d+)/i', $ua, $matches)) {
            $browser = 'Microsoft Edge ' . ($matches[1] ?? '');
        } elseif (preg_match('/opr\/(\d+)|opera\/(\d+)/i', $ua, $matches)) {
            $browser = 'Opera ' . ($matches[1] ?? $matches[2] ?? '');
        } elseif (preg_match('/chrome\/(\d+)/i', $ua, $matches)) {
            $browser = 'Google Chrome ' . ($matches[1] ?? '');
        } elseif (preg_match('/firefox\/(\d+)/i', $ua, $matches)) {
            $browser = 'Mozilla Firefox ' . ($matches[1] ?? '');
        } elseif (preg_match('/version\/(\d+).*safari/i', $ua, $matches)) {
            $browser = 'Apple Safari ' . ($matches[1] ?? '');
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = 'Apple Safari';
        }

        return [
            'browser'     => trim($browser),
            'platform'    => trim($platform),
            'device_type' => $deviceType,
        ];
    }

    /**
     * Dapatkan Geo IP Location (Kota, Provinsi, Negara, Koordinat Lat/Lng)
     */
    public function getIpLocation(string $ip): array
    {
        // Default Sekolah (SMK PLUS AL HILAL - Cirebon / Jawa Barat)
        $default = [
            'country'      => 'Indonesia',
            'country_code' => 'ID',
            'region'       => 'Jawa Barat',
            'city'         => 'Cirebon',
            'latitude'     => -6.7320,
            'longitude'    => 108.5523,
            'timezone'     => 'Asia/Jakarta',
            'isp'          => 'Jaringan Lokal / Intranet (SMK Al Hilal)',
        ];

        // Jika IP lokal / loopback / private range
        if ($this->isPrivateIp($ip)) {
            return $default;
        }

        // Cache 30 hari per IP agar performa super cepat dan hemat limit API
        return Cache::remember("geoip_{$ip}", 86400 * 30, function () use ($ip, $default) {
            try {
                $response = Http::timeout(2)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,message,country,countryCode,regionName,city,lat,lon,timezone,isp'
                ]);

                if ($response->successful() && ($response->json('status') === 'success')) {
                    $data = $response->json();
                    return [
                        'country'      => $data['country'] ?? 'Indonesia',
                        'country_code' => $data['countryCode'] ?? 'ID',
                        'region'       => $data['regionName'] ?? 'Jawa Barat',
                        'city'         => $data['city'] ?? 'Cirebon',
                        'latitude'     => (float) ($data['lat'] ?? -6.7320),
                        'longitude'    => (float) ($data['lon'] ?? 108.5523),
                        'timezone'     => $data['timezone'] ?? 'Asia/Jakarta',
                        'isp'          => $data['isp'] ?? 'Internet Provider',
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("GeoIP lookup failed for IP {$ip}: " . $e->getMessage());
            }

            return $default;
        });
    }

    /**
     * Cek apakah IP adalah private atau localhost
     */
    protected function isPrivateIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost', '0.0.0.0'])) {
            return true;
        }

        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Buat Data Simulasi Realistis Login dari berbagai Kota di Indonesia
     * Berguna agar peta dan analisis langsung terlihat informatif dan siap pakai
     */
    public function generateRealisticSampleLogs(int $count = 35): int
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return 0;
        }

        $indonesianCities = [
            ['city' => 'Cirebon', 'region' => 'Jawa Barat', 'lat' => -6.7320, 'lng' => 108.5523, 'isp' => 'PT Telkom Indonesia'],
            ['city' => 'Bandung', 'region' => 'Jawa Barat', 'lat' => -6.9175, 'lng' => 107.6191, 'isp' => 'PT Indosat Ooredoo'],
            ['city' => 'Jakarta', 'region' => 'DKI Jakarta', 'lat' => -6.2088, 'lng' => 106.8456, 'isp' => 'Biznet Networks'],
            ['city' => 'Bekasi', 'region' => 'Jawa Barat', 'lat' => -6.2383, 'lng' => 106.9756, 'isp' => 'First Media'],
            ['city' => 'Kuningan', 'region' => 'Jawa Barat', 'lat' => -6.9765, 'lng' => 108.4831, 'isp' => 'PT Telkom Indonesia'],
            ['city' => 'Majalengka', 'region' => 'Jawa Barat', 'lat' => -6.8361, 'lng' => 108.2275, 'isp' => 'Telkomsel'],
            ['city' => 'Indramayu', 'region' => 'Jawa Barat', 'lat' => -6.3264, 'lng' => 108.3200, 'isp' => 'XL Axiata'],
            ['city' => 'Semarang', 'region' => 'Jawa Tengah', 'lat' => -6.9667, 'lng' => 110.4167, 'isp' => 'PT Telkom Indonesia'],
            ['city' => 'Yogyakarta', 'region' => 'DI Yogyakarta', 'lat' => -7.7956, 'lng' => 110.3695, 'isp' => 'Biznet Networks'],
            ['city' => 'Surabaya', 'region' => 'Jawa Timur', 'lat' => -7.2575, 'lng' => 112.7521, 'isp' => 'MyRepublic'],
            ['city' => 'Medan', 'region' => 'Sumatera Utara', 'lat' => 3.5952, 'lng' => 98.6722, 'isp' => 'PT Telkom Indonesia'],
            ['city' => 'Makassar', 'region' => 'Sulawesi Selatan', 'lat' => -5.1477, 'lng' => 119.4327, 'isp' => 'Telkomsel'],
            ['city' => 'Denpasar', 'region' => 'Bali', 'lat' => -8.6705, 'lng' => 115.2126, 'isp' => 'Biznet Networks'],
        ];

        $browsers = [
            ['browser' => 'Google Chrome 128', 'platform' => 'Windows 10/11', 'device_type' => 'Desktop'],
            ['browser' => 'Google Chrome Mobile', 'platform' => 'Android 14', 'device_type' => 'Mobile'],
            ['browser' => 'Apple Safari', 'platform' => 'iOS 18.0', 'device_type' => 'Mobile'],
            ['browser' => 'Microsoft Edge 128', 'platform' => 'Windows 10/11', 'device_type' => 'Desktop'],
            ['browser' => 'Mozilla Firefox 130', 'platform' => 'Linux', 'device_type' => 'Desktop'],
            ['browser' => 'Samsung Internet', 'platform' => 'Android 13', 'device_type' => 'Mobile'],
        ];

        $created = 0;
        for ($i = 0; $i < $count; $i++) {
            $user = $users->random();
            $loc = $indonesianCities[array_rand($indonesianCities)];
            $dev = $browsers[array_rand($browsers)];

            // 85% success, 15% failed
            $isSuccess = (rand(1, 100) <= 88);
            $randomDaysAgo = rand(0, 14);
            $randomHoursAgo = rand(0, 23);
            $randomMinutesAgo = rand(0, 59);
            $time = now()->subDays($randomDaysAgo)->subHours($randomHoursAgo)->subMinutes($randomMinutesAgo);

            $ip = '114.124.' . rand(1, 254) . '.' . rand(1, 254);

            LoginLog::create([
                'user_id'       => $user->id,
                'login_input'   => $user->email ?? $user->username,
                'user_name'     => $user->name ?? $user->nama_lengkap ?? $user->username,
                'user_role'     => $user->role ?? 'siswa',
                'ip_address'    => $ip,
                'status'        => $isSuccess ? 'success' : 'failed',
                'failure_reason'=> $isSuccess ? null : 'Kata sandi tidak sesuai',
                'user_agent'    => "Mozilla/5.0 ({$dev['platform']}) AppleWebKit/537.36 {$dev['browser']}",
                'device_type'   => $dev['device_type'],
                'browser'       => $dev['browser'],
                'platform'      => $dev['platform'],
                'country'       => 'Indonesia',
                'country_code'  => 'ID',
                'region'        => $loc['region'],
                'city'          => $loc['city'],
                'latitude'      => $loc['lat'] + (rand(-50, 50) / 10000), // Sedikit variasi koordinat
                'longitude'     => $loc['lng'] + (rand(-50, 50) / 10000),
                'timezone'      => 'Asia/Jakarta',
                'isp'           => $loc['isp'],
                'is_suspicious' => !$isSuccess && rand(1, 10) === 1,
                'login_at'      => $time,
                'created_at'    => $time,
                'updated_at'    => $time,
            ]);
            $created++;
        }

        return $created;
    }
}
