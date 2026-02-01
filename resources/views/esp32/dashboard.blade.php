<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Device Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            <i class="fas fa-microchip text-blue-600"></i>
                            ESP32 Device Manager
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Verwalte deine ESP32-Geräte im Netzwerk</p>
                    </div>
                    <button onclick="runDiscovery()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        <i class="fas fa-search mr-2"></i>
                        Netzwerk scannen
                    </button>
                </div>
            </div>
        </header>

        <!-- Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-microchip text-2xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Gesamt</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-circle text-2xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Online</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['online'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-circle text-2xl text-red-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Offline</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['offline'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Device List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">
                        <i class="fas fa-list mr-2"></i>
                        Gefundene Geräte
                    </h2>
                </div>

                @if($devices->isEmpty())
                <div class="p-12 text-center">
                    <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg">Keine Geräte gefunden</p>
                    <p class="text-gray-500 text-sm mt-2">Starte einen Netzwerk-Scan um Geräte zu finden</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hostname
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP-Adresse
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Intervall
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Zuletzt gesehen
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aktionen
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($devices as $device)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($device->isOnline())
                                    <span class="flex items-center text-green-600">
                                        <i class="fas fa-circle text-xs mr-2"></i>
                                        Online
                                    </span>
                                    @else
                                    <span class="flex items-center text-red-600">
                                        <i class="fas fa-circle text-xs mr-2"></i>
                                        Offline
                                    </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-microchip text-blue-600 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-900">{{ $device->hostname }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $device->ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form method="POST" action="{{ route('esp32.update', $device->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="interval" value="{{ $device->interval }}" 
                                               min="5" max="86400" step="5"
                                               class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                                        <span class="text-xs text-gray-500">s</span>
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs">
                                            <i class="fas fa-save"></i>
                                        </button>
                                    </form>
                                    <span class="text-xs text-gray-500 mt-1 block">{{ $device->intervalHuman() }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($device->last_seen)
                                        {{ $device->last_seen->diffForHumans() }}
                                    @else
                                        <span class="text-gray-400">Nie</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('esp32.toggle', $device->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-800">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('esp32.destroy', $device->id) }}" 
                                              class="inline" onsubmit="return confirm('Gerät wirklich löschen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Quick Interval Presets -->
            <div class="mt-8 bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-clock mr-2"></i>
                    Intervall-Presets
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                    <button onclick="setAllIntervals(30)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                        30s (Test)
                    </button>
                    <button onclick="setAllIntervals(300)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                        5 Minuten
                    </button>
                    <button onclick="setAllIntervals(600)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                        10 Minuten
                    </button>
                    <button onclick="setAllIntervals(1800)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                        30 Minuten
                    </button>
                    <button onclick="setAllIntervals(3600)" class="px-4 py-2 bg-blue-100 hover:bg-blue-200 rounded-lg text-sm transition">
                        1 Stunde
                    </button>
                    <button onclick="setAllIntervals(86400)" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm transition">
                        24 Stunden
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function runDiscovery() {
            alert('Network-Scan läuft im Hintergrund. Dies kann einige Minuten dauern.\n\nFühre aus: php artisan esp32:discover');
            // In Production würde man hier einen AJAX-Call machen oder einen Job dispatchen
        }

        function setAllIntervals(seconds) {
            if (confirm(`Alle Geräte auf ${seconds}s setzen?`)) {
                document.querySelectorAll('input[name="interval"]').forEach(input => {
                    input.value = seconds;
                });
            }
        }
    </script>
</body>
</html>
