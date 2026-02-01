# Laravel Dashboard - Installations- und Nutzungsanleitung

## 📁 Dateien in dein Laravel-Projekt kopieren

### 1. Migration
Kopiere die Migration in dein Projekt:
```bash
cp database/migrations/2024_01_01_000000_create_tiles_table.php \
   DEIN_LARAVEL_PROJEKT/database/migrations/
```

### 2. Model
Kopiere das Model:
```bash
cp app/Models/Tile.php \
   DEIN_LARAVEL_PROJEKT/app/Models/
```

### 3. Controller
Kopiere den Controller:
```bash
cp app/Http/Controllers/TileController.php \
   DEIN_LARAVEL_PROJEKT/app/Http/Controllers/
```

### 4. Views
Kopiere die Views:
```bash
# Layout
cp resources/views/layouts/app.blade.php \
   DEIN_LARAVEL_PROJEKT/resources/views/layouts/

# Dashboard View (erstelle erst das Verzeichnis)
mkdir -p DEIN_LARAVEL_PROJEKT/resources/views/dashboard
cp resources/views/dashboard/index.blade.php \
   DEIN_LARAVEL_PROJEKT/resources/views/dashboard/
```

### 5. Seeder (optional)
Kopiere den Seeder für die Initialdaten:
```bash
cp database/seeders/TileSeeder.php \
   DEIN_LARAVEL_PROJEKT/database/seeders/
```

### 6. Routen
Füge die Routen in `routes/web.php` hinzu:
```php
use App\Http\Controllers\TileController;

Route::get('/', [TileController::class, 'index'])->name('dashboard.index');
Route::post('/tiles', [TileController::class, 'store'])->name('tiles.store');
Route::delete('/tiles/{tile}', [TileController::class, 'destroy'])->name('tiles.destroy');
```

## 🚀 Installation & Setup

### 1. Migration ausführen
```bash
php artisan migrate
```

### 2. Initialdaten einfügen (optional)
```bash
php artisan db:seed --class=TileSeeder
```

Oder manuell in der Datenbank:
```sql
INSERT INTO tiles (name, url, `order`, created_at, updated_at) VALUES
('PhpMyAdmin', 'http://192.168.178.91:8080/', 1, NOW(), NOW()),
('PiMox', 'https://192.168.178.71:8006/', 2, NOW(), NOW()),
('Home Assistant', 'http://homeassistant.local:8123/', 3, NOW(), NOW()),
('Pi Hole', 'https://192.168.178.93', 4, NOW(), NOW());
```

### 3. Server starten
```bash
php artisan serve
```

## 📋 Features

✅ **CRUD-Funktionalität**
- Alle Kacheln anzeigen
- Neue Kacheln hinzufügen
- Kacheln löschen

✅ **Responsive Design**
- Funktioniert auf Desktop, Tablet und Smartphone
- Grid-Layout passt sich automatisch an

✅ **Laravel Best Practices**
- Model-View-Controller Pattern
- Form Validation
- CSRF Protection
- Session Flash Messages
- Route Model Binding

✅ **Benutzerfreundlich**
- Modal zum Hinzufügen
- Bestätigungsdialog beim Löschen
- Hover-Effekte
- Auto-Hiding Alerts

## 📝 Verwendung

1. **Dashboard aufrufen**: Öffne `http://localhost:8000` (oder deine konfigurierte URL)
2. **Kachel hinzufügen**: Klicke auf "+ Neue Kachel hinzufügen"
3. **Kachel löschen**: Fahre mit der Maus über eine Kachel und klicke auf "Löschen"

## 🔧 Anpassungen

### Design ändern
Das CSS findest du in `resources/views/layouts/app.blade.php` im `<style>`-Tag.

### Validierung anpassen
Die Validierung findest du im Controller unter `TileController@store`.

### Weitere Felder hinzufügen
1. Migration bearbeiten und neue Spalten hinzufügen
2. Model: `$fillable` Array erweitern
3. Controller: Validierung erweitern
4. View: Formularfelder hinzufügen

## 📊 Datenbankstruktur

**Tabelle: tiles**
- `id` (Primary Key)
- `name` (String) - Name der Kachel
- `url` (String) - URL zum Ziel
- `order` (Integer) - Sortierreihenfolge
- `created_at` (Timestamp)
- `updated_at` (Timestamp)

## 🎨 Erweitungsmöglichkeiten

Zukünftige Features könnten sein:
- [ ] Kacheln per Drag & Drop sortieren
- [ ] Icons/Farben für Kacheln
- [ ] Kategorien/Gruppen
- [ ] Benutzer-Authentifizierung
- [ ] Edit-Funktion für bestehende Kacheln
- [ ] Import/Export von Kacheln

## 💡 Tipps

- Die Kacheln werden nach dem `order`-Feld und dann nach `id` sortiert
- Alle Links öffnen sich in einem neuen Tab (`target="_blank"`)
- Die Alerts verschwinden automatisch nach 3 Sekunden
- Das Modal lässt sich mit ESC schließen
