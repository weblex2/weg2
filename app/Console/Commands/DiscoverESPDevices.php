<?php

namespace App\Console\Commands;

use App\Models\ESP32Device;
use Illuminate\Console\Command;

class DiscoverESPDevices extends Command
{
    protected $signature = 'esp32:discover
                            {--subnet=192.168.178.0/24 : Subnet to scan}
                            {--timeout=1 : Ping timeout in seconds}';

    protected $description = 'Discover ESP32 devices on the network';

    public function handle(): int
    {
        $subnet = $this->option('subnet');
        $timeout = $this->option('timeout');

        $this->info("🔍 Scanning network: {$subnet}");
        $this->info("⏱️  Timeout: {$timeout}s per host");
        $this->newLine();

        // Parse subnet
        [$network, $cidr] = explode('/', $subnet);
        $range = $this->getIPRange($network, $cidr);

        $bar = $this->output->createProgressBar(count($range));
        $bar->start();

        $discovered = 0;

        foreach ($range as $ip) {
            $hostname = $this->getHostname($ip, $timeout);
            $this->info("🔍 Scan IP: {$ip}");
            if ($hostname!=null){
                $this->info("🔍 Gefunden: {$hostname}");
            }
            if ($hostname && str_starts_with(strtolower($hostname), 'Feu')) {
                $this->newLine();
                $this->line("✅ Found: <info>{$hostname}</info> at <comment>{$ip}</comment>");

                // Save or update device
                $device = ESP32Device::updateOrCreate(
                    ['hostname' => $hostname],
                    [
                        'device_id' => $hostname,
                        'ip_address' => $ip,
                        'last_seen' => now(),
                        'is_active' => true,
                    ]
                );

                $discovered++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($discovered > 0) {
            $this->info("🎉 Discovered {$discovered} ESP device(s)");
        } else {
            $this->warn("⚠️  No ESP devices found");
        }

        return self::SUCCESS;
    }

    private function getIPRange(string $network, int $cidr): array
    {
        $range = [];
        $start = ip2long($network);
        $end = $start + pow(2, (32 - $cidr)) - 1;

        // Limit to reasonable range for home networks
        $max = min($end, $start + 254);

        for ($ip = $start + 1; $ip < $max; $ip++) {
            $range[] = long2ip($ip);
        }

        return $range;
    }

    private function getHostname(string $ip, int $timeout): ?string
    {
        // Method 1: Try ping first (fast check)
        $ping = shell_exec("ping -c 1 -W {$timeout} {$ip} 2>&1");
        if (!str_contains($ping, '1 received')) {
            return null;
        }

        // Method 2: Try to get hostname via DNS
        $hostname = gethostbyaddr($ip);
        if ($hostname !== $ip) {
            return $hostname;
        }

        // Method 3: Try nmap if available (more reliable)
        if ($this->commandExists('nmap')) {
            $nmap = shell_exec("nmap -sn {$ip} 2>&1 | grep 'Nmap scan report'");
            if (preg_match('/for (.+?) \(/', $nmap, $matches)) {
                return trim($matches[1]);
            }
        }

        // Method 4: Try avahi/bonjour if available
        if ($this->commandExists('avahi-resolve-address')) {
            $avahi = shell_exec("avahi-resolve-address {$ip} 2>&1");
            if (preg_match('/\s+(.+?)\.local/', $avahi, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    private function commandExists(string $command): bool
    {
        $return = shell_exec("which {$command}");
        return !empty($return);
    }
}
