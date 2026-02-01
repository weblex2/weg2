# ESP32 Intervall-Steuerung über Laravel Webservice

## Setup-Anleitung

### 1. Laravel Backend

**Controller erstellen:**
```bash
php artisan make:controller Api/ESP32IntervalController
```

Dann den Inhalt von `ESP32IntervalController.php` einfügen.

**Routes registrieren:**
In `routes/api.php` die Routes aus `api_routes.php` hinzufügen.

**Cache-Driver prüfen:**
In `.env` sicherstellen dass ein Cache-Driver konfiguriert ist:
```env
CACHE_DRIVER=file  # oder redis, memcached
```

**Testen:**
```bash
# Intervall setzen
curl -X POST http://YOUR_PROXMOX_IP/api/esp32/interval \
  -H "Content-Type: application/json" \
  -d '{"device_id":"esp32_moisture_001","interval":300}'

# Intervall abrufen
curl http://YOUR_PROXMOX_IP/api/esp32/interval?device_id=esp32_moisture_001

# Status prüfen
curl http://YOUR_PROXMOX_IP/api/esp32/status?device_id=esp32_moisture_001
```

### 2. ESP32 Code

**Wichtig anpassen:**
```cpp
const char* laravel_api_url = "http://192.168.1.XXX/api/esp32/interval";
```

**Benötigte Library:**
- HTTPClient (bereits in ESP32 Arduino Core enthalten)

**Upload:**
1. Code in Arduino IDE öffnen
2. `laravel_api_url` anpassen
3. Auf ESP32 hochladen

### 3. Home Assistant Configuration

**In `configuration.yaml` einfügen:**
Die Konfiguration aus `home_assistant_config.yaml` übernehmen und anpassen:
- `YOUR_PROXMOX_IP` durch echte IP ersetzen

**Home Assistant neu laden:**
```
Entwicklerwerkzeuge > YAML > Alle YAML-Konfigurationen neu laden
```

**Dashboard-Karte erstellen:**
```yaml
type: entities
title: ESP32 Feuchtigkeitssensor
entities:
  - entity_id: sensor.bodenfeuchtigkeit
    name: Bodenfeuchtigkeit
  - entity_id: input_number.esp32_moisture_interval
    name: Messintervall
  - entity_id: sensor.esp32_moisture_interval
    name: Aktuelles Intervall
```

## Workflow

1. **Intervall in Home Assistant ändern:**
   - Input Number Slider bewegen
   - Automation sendet neuen Wert an Laravel API
   - Laravel speichert Wert im Cache

2. **ESP32 wacht auf:**
   - Verbindet sich mit WiFi
   - Fragt Laravel API nach aktuellem Intervall
   - Aktualisiert `sleepDuration` falls nötig
   - Sendet Messwert via MQTT
   - Geht mit neuem Intervall schlafen

3. **Sofortige Änderung:**
   - Keine Wartezeit mehr!
   - ESP32 holt sich beim nächsten Boot das neue Intervall

## Vorteile

✅ **Sofortige Wirkung**: Intervall wird beim nächsten Boot aktiv
✅ **Kein MQTT-Waiting**: ESP32 muss nicht auf Nachrichten warten
✅ **Zentrale Verwaltung**: Laravel als Single Source of Truth
✅ **Einfach erweiterbar**: Weitere Devices einfach hinzufügbar
✅ **Status-Monitoring**: "Last Seen" und Online-Status

## Optional: Security

Wenn du die API absichern willst:

**Laravel Sanctum:**
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Dann in routes mit `auth:sanctum` Middleware schützen.

**ESP32:**
```cpp
http.addHeader("Authorization", "Bearer YOUR_API_TOKEN");
```

## Troubleshooting

**ESP32 bekommt keine Response:**
- IP-Adresse korrekt?
- Laravel läuft?
- Firewall-Regeln prüfen

**Intervall wird nicht gespeichert:**
- Cache-Driver in Laravel aktiv?
- `storage/framework/cache` Ordner beschreibbar?

**Home Assistant bekommt keine Updates:**
- REST Sensor `scan_interval` zu hoch?
- Laravel API erreichbar von HA?
