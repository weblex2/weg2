# Laravel E-Mail Import System

Ein vollständiges Laravel 12 System zum Import von E-Mails aus IMAP-Servern (Strato) und per Drag & Drop Upload (.eml/.msg Dateien).

## 🚀 Features

- ✅ **IMAP Import** von Strato-Servern
- ✅ **Drag & Drop Upload** für .eml und .msg Dateien
- ✅ **Vollständige E-Mail-Speicherung** (Subject, Body, Anhänge, Header)
- ✅ **Anhang-Verwaltung** mit Download-Funktion
- ✅ **Datumsbereich-Import**
- ✅ **CLI-Befehle** für automatisierten Import
- ✅ **Moderne Web-Oberfläche** mit Tailwind CSS
- ✅ **E-Mail-Detailansicht** mit HTML/Text/Header-Tabs

## 📋 Voraussetzungen

- PHP >= 8.2
- Composer
- MySQL/MariaDB
- Laravel 12
- IMAP-Zugang zu deinem Strato-Server

## 🔧 Installation

### 1. Projekt einrichten

```bash
# In dein bestehendes Laravel 12 Projekt wechseln
cd dein-laravel-projekt

# Benötigte Packages installieren
composer require webklex/laravel-imap
composer require php-mime-mail-parser/php-mime-mail-parser
```

### 2. Dateien kopieren

Kopiere die folgenden Dateien in dein Projekt:

```
app/
├── Models/
│   ├── Email.php
│   └── EmailAttachment.php
├── Services/
│   ├── ImapEmailImportService.php
│   └── EmlFileImportService.php
├── Http/Controllers/
│   └── EmailImportController.php
└── Console/Commands/
    └── ImportEmailsCommand.php

database/migrations/
└── 2024_01_31_000001_create_emails_table.php

resources/views/emails/
├── import.blade.php
└── show.blade.php

config/
└── imap.php

routes/
└── web.php (Routes hinzufügen)
```

### 3. Umgebungsvariablen konfigurieren

Füge folgende Variablen in deine `.env` Datei ein:

```env
# IMAP Configuration (Strato)
IMAP_HOST=imap.strato.de
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_VALIDATE_CERT=true
IMAP_USERNAME=deine@email.de
IMAP_PASSWORD=deinPasswort
IMAP_PROTOCOL=imap
```

### 4. Datenbank migrieren

```bash
php artisan migrate
```

### 5. Storage Link erstellen

```bash
php artisan storage:link
```

### 6. IMAP Config veröffentlichen (optional)

```bash
php artisan vendor:publish --provider="Webklex\IMAP\Providers\LaravelServiceProvider"
```

## 📖 Verwendung

### Web-Interface

Öffne in deinem Browser:

```
http://localhost:8000/emails/import
```

#### IMAP Import:
1. Gib den IMAP-Ordner ein (Standard: INBOX)
2. Optional: Setze ein Limit oder wähle "Nur ungelesene"
3. Klicke auf "E-Mails importieren"

#### Drag & Drop Upload:
1. Ziehe .eml oder .msg Dateien in den Dropzone-Bereich
2. Oder klicke zum Durchsuchen
3. Klicke auf "Hochladen und Importieren"

### CLI-Befehle

#### Alle E-Mails aus INBOX importieren:
```bash
php artisan emails:import
```

#### Nur 50 E-Mails importieren:
```bash
php artisan emails:import --limit=50
```

#### Nur ungelesene E-Mails:
```bash
php artisan emails:import --unread
```

#### E-Mails aus bestimmtem Ordner:
```bash
php artisan emails:import --folder=Sent
```

#### E-Mails aus Datumsbereich:
```bash
php artisan emails:import --from=2024-01-01 --to=2024-01-31
```

## 🗂️ Datenbankstruktur

### Tabelle: `emails`
- `id` - Primärschlüssel
- `message_id` - Eindeutige E-Mail-ID
- `subject` - Betreff
- `from_email` - Absender-Email
- `from_name` - Absender-Name
- `to` - Empfänger (JSON)
- `cc` - CC-Empfänger (JSON)
- `bcc` - BCC-Empfänger (JSON)
- `text_body` - Text-Version
- `html_body` - HTML-Version
- `email_date` - E-Mail-Datum
- `source` - Quelle (imap/upload)
- `has_attachments` - Hat Anhänge
- `headers` - Alle Header (JSON)

### Tabelle: `email_attachments`
- `id` - Primärschlüssel
- `email_id` - Foreign Key zu emails
- `filename` - Dateiname
- `mime_type` - MIME-Type
- `size` - Größe in Bytes
- `path` - Speicherpfad

## 🔍 API Endpoints

### E-Mails auflisten
```http
GET /emails
```

Query-Parameter:
- `from_email` - Filter nach Absender
- `date_from` - Von Datum
- `date_to` - Bis Datum
- `source` - Filter nach Quelle (imap/upload)
- `per_page` - Anzahl pro Seite (Standard: 50)

### E-Mail anzeigen
```http
GET /emails/{id}
```

### IMAP Import
```http
POST /emails/import/imap
```

Body:
```json
{
    "folder": "INBOX",
    "limit": 50,
    "unread_only": true
}
```

### Datumsbereich Import
```http
POST /emails/import/imap/daterange
```

Body:
```json
{
    "from": "2024-01-01",
    "to": "2024-01-31",
    "folder": "INBOX"
}
```

### Upload Import
```http
POST /emails/import/upload
```

Body: Multipart/form-data mit `files[]`

## 🎨 Anpassungen

### E-Mail-Felder erweitern

Wenn du zusätzliche Felder speichern möchtest:

1. Migration erweitern:
```bash
php artisan make:migration add_fields_to_emails_table
```

2. In der Migration:
```php
Schema::table('emails', function (Blueprint $table) {
    $table->string('dein_neues_feld')->nullable();
});
```

3. Model erweitern (`app/Models/Email.php`):
```php
protected $fillable = [
    // ... bestehende Felder
    'dein_neues_feld',
];
```

### Weitere IMAP-Server

Für andere E-Mail-Provider, ändere in `.env`:

**Gmail:**
```env
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
```

**Outlook/Office365:**
```env
IMAP_HOST=outlook.office365.com
IMAP_PORT=993
```

**1&1:**
```env
IMAP_HOST=imap.1und1.de
IMAP_PORT=993
```

## 🔐 Sicherheit

- Stelle sicher, dass `storage/app/email-attachments` nicht öffentlich zugänglich ist
- Verwende starke Passwörter für IMAP-Zugänge
- Setze entsprechende File-Upload-Limits in `php.ini`:
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

## 🐛 Troubleshooting

### "Connection refused" beim IMAP-Import
- Überprüfe IMAP-Zugangsdaten in `.env`
- Stelle sicher, dass IMAP auf dem Server aktiviert ist
- Prüfe Firewall-Einstellungen (Port 993 muss offen sein)

### Anhänge werden nicht angezeigt
```bash
php artisan storage:link
```

### "Class not found" Fehler
```bash
composer dump-autoload
```

### Upload-Limit überschritten
Erhöhe in `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 20M
```

## 📝 Lizenz

MIT License

## 🤝 Support

Bei Fragen oder Problemen, erstelle ein Issue im Repository.
