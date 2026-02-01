<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ESP32Device;
use Illuminate\Http\Request;

class ESP32IntervalController extends Controller
{
    // ESP32 fragt Intervall ab (wird bei jedem Boot aufgerufen)
    public function getInterval(Request $request)
    {
        $deviceId = $request->input('device_id');
        $hostname = $request->input('hostname');
        $ipAddress = $request->ip();

        if (!$deviceId && !$hostname) {
            return response()->json([
                'error' => 'device_id or hostname required'
            ], 400);
        }

        // Device finden oder erstellen
        $device = ESP32Device::firstOrCreate(
            ['device_id' => $deviceId ?? $hostname],
            [
                'hostname' => $hostname,
                'ip_address' => $ipAddress,
                'interval' => 3600, // Default: 1 Stunde
                'is_active' => true,
            ]
        );

        // Last seen aktualisieren
        $device->updateLastSeen();

        // IP aktualisieren falls geändert
        if ($device->ip_address !== $ipAddress) {
            $device->update(['ip_address' => $ipAddress]);
        }

        return response()->json([
            'interval' => $device->interval,
            'device_id' => $device->device_id,
            'timestamp' => now()->toIso8601String(),
            'message' => 'Sleep well! 😴'
        ]);
    }

    // Web/API: Intervall setzen
    public function setInterval(Request $request, $id = null)
    {
        // Support both route parameter and request body
        $deviceId = $id ?? $request->input('device_id');

        $validated = $request->validate([
            'interval' => 'required|integer|min:5|max:86400'
        ]);

        $device = ESP32Device::where('device_id', $deviceId)->firstOrFail();
        $device->update(['interval' => $validated['interval']]);

        return response()->json([
            'success' => true,
            'device_id' => $device->device_id,
            'interval' => $device->interval,
            'interval_human' => $device->intervalHuman(),
            'message' => "Interval updated to {$device->intervalHuman()}"
        ]);
    }

    // Status eines Devices
    public function getStatus(Request $request, $id = null)
    {
        $deviceId = $id ?? $request->input('device_id');
        $device = ESP32Device::where('device_id', $deviceId)->firstOrFail();

        return response()->json([
            'device_id' => $device->device_id,
            'hostname' => $device->hostname,
            'ip_address' => $device->ip_address,
            'interval' => $device->interval,
            'interval_human' => $device->intervalHuman(),
            'last_seen' => $device->last_seen?->toIso8601String(),
            'online' => $device->isOnline(),
            'is_active' => $device->is_active,
        ]);
    }

    // Alle Devices auflisten
    public function index()
    {
        $devices = ESP32Device::active()
            ->orderBy('last_seen', 'desc')
            ->get()
            ->map(fn($device) => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'hostname' => $device->hostname,
                'ip_address' => $device->ip_address,
                'interval' => $device->interval,
                'interval_human' => $device->intervalHuman(),
                'last_seen' => $device->last_seen?->toIso8601String(),
                'online' => $device->isOnline(),
            ]);

        return response()->json($devices);
    }

    // ESP32IntervalController.php

    public function register(Request $request)
    {
        $deviceId = $request->input('device_id');
        $hostname = $request->input('hostname');
        $ipAddress = $request->ip();

        if (!$deviceId || !$hostname) {
            return response()->json(['error' => 'device_id and hostname required'], 400);
        }

        $device = ESP32Device::updateOrCreate(
            ['device_id' => $deviceId],
            [
                'hostname' => $hostname,
                'ip_address' => $ipAddress,
                'interval' => 3600,
                'is_active' => true,
            ]
        );

        $device->updateLastSeen();

        return response()->json([
            'success' => true,
            'device_id' => $device->device_id,
            'hostname' => $device->hostname,
            'interval' => $device->interval,
            'message' => 'Device registered successfully'
        ]);
    }


}
