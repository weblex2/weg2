<?php

namespace App\Http\Controllers;

use App\Models\ESP32Device;
use Illuminate\Http\Request;

class ESP32DashboardController extends Controller
{
    public function index()
    {
        $devices = ESP32Device::active()
            ->orderBy('last_seen', 'desc')
            ->get();

        $stats = [
            'total' => $devices->count(),
            'online' => $devices->filter->isOnline()->count(),
            'offline' => $devices->filter(fn($d) => !$d->isOnline())->count(),
        ];

        return view('esp32.dashboard', compact('devices', 'stats'));
    }

    public function show($id)
    {
        $device = ESP32Device::findOrFail($id);
        return view('esp32.show', compact('device'));
    }

    public function update(Request $request, $id)
    {
        $device = ESP32Device::findOrFail($id);
        
        $validated = $request->validate([
            'interval' => 'required|integer|min:5|max:86400',
        ]);

        $device->update($validated);

        return redirect()
            ->route('esp32.dashboard')
            ->with('success', "Interval für {$device->hostname} auf {$device->intervalHuman()} gesetzt");
    }

    public function toggleActive($id)
    {
        $device = ESP32Device::findOrFail($id);
        $device->update(['is_active' => !$device->is_active]);

        return redirect()
            ->route('esp32.dashboard')
            ->with('success', "Device {$device->hostname} " . ($device->is_active ? 'aktiviert' : 'deaktiviert'));
    }

    public function destroy($id)
    {
        $device = ESP32Device::findOrFail($id);
        $hostname = $device->hostname;
        $device->delete();

        return redirect()
            ->route('esp32.dashboard')
            ->with('success', "Device {$hostname} gelöscht");
    }
}
