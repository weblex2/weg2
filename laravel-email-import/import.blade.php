<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Mail Import</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">E-Mail Import</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- IMAP Import -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">📥 IMAP Import (Strato Server)</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Ordner</label>
                        <input type="text" id="imap-folder" value="INBOX" 
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Anzahl (optional)</label>
                        <input type="number" id="imap-limit" placeholder="Alle" min="1" max="1000"
                               class="w-full px-3 py-2 border rounded-md">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="imap-unread" class="mr-2">
                        <label for="imap-unread" class="text-sm">Nur ungelesene E-Mails</label>
                    </div>
                    
                    <button onclick="importFromImap()" 
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                        E-Mails importieren
                    </button>
                </div>

                <div class="mt-6 border-t pt-4">
                    <h3 class="font-semibold mb-2">Datumsbereich Import</h3>
                    <div class="space-y-2">
                        <input type="date" id="date-from" class="w-full px-3 py-2 border rounded-md">
                        <input type="date" id="date-to" class="w-full px-3 py-2 border rounded-md">
                        <button onclick="importByDateRange()" 
                                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                            Import nach Datum
                        </button>
                    </div>
                </div>
            </div>

            <!-- Drag & Drop Upload -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">📤 Drag & Drop Upload</h2>
                
                <div id="dropzone" 
                     class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-blue-500 transition-colors cursor-pointer">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-600">
                        <span class="font-semibold">Klicken zum Hochladen</span> oder per Drag & Drop
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        .eml oder .msg Dateien (max. 10MB pro Datei)
                    </p>
                    <input type="file" id="file-input" multiple accept=".eml,.msg" class="hidden">
                </div>

                <div id="file-list" class="mt-4 space-y-2"></div>
                
                <button id="upload-btn" onclick="uploadFiles()" 
                        class="w-full mt-4 bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        disabled>
                    Hochladen und Importieren
                </button>
            </div>
        </div>

        <!-- Status Messages -->
        <div id="status-message" class="hidden rounded-lg p-4 mb-8"></div>

        <!-- Kürzlich importierte E-Mails -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">📧 Kürzlich importierte E-Mails</h2>
            
            @if($recentEmails->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Datum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Von</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Betreff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quelle</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Anhänge</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentEmails as $email)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{ $email->email_date->format('d.m.Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <div class="font-medium">{{ $email->from_name ?? $email->from_email }}</div>
                                        @if($email->from_name)
                                            <div class="text-gray-500 text-xs">{{ $email->from_email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('emails.show', $email) }}" class="text-blue-600 hover:underline">
                                            {{ $email->subject ?: '(Kein Betreff)' }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                     {{ $email->source === 'imap' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ strtoupper($email->source) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($email->has_attachments)
                                            <span class="text-gray-600">📎 {{ $email->attachments->count() }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">Noch keine E-Mails importiert</p>
            @endif
        </div>
    </div>

    <script>
        let selectedFiles = [];

        // CSRF Token für alle AJAX Requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Drag & Drop Funktionalität
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const fileList = document.getElementById('file-list');
        const uploadBtn = document.getElementById('upload-btn');

        dropzone.addEventListener('click', () => fileInput.click());

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('border-blue-500', 'bg-blue-50');
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('border-blue-500', 'bg-blue-50');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            displayFileList();
            uploadBtn.disabled = selectedFiles.length === 0;
        }

        function displayFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'flex items-center justify-between bg-gray-50 p-2 rounded';
                div.innerHTML = `
                    <span class="text-sm text-gray-700">${file.name} (${formatFileSize(file.size)})</span>
                    <button onclick="removeFile(${index})" class="text-red-500 hover:text-red-700">
                        ✕
                    </button>
                `;
                fileList.appendChild(div);
            });
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            displayFileList();
            uploadBtn.disabled = selectedFiles.length === 0;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Upload Funktion
        async function uploadFiles() {
            if (selectedFiles.length === 0) return;

            const formData = new FormData();
            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });

            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';

            try {
                const response = await fetch('{{ route("emails.import.upload") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');
                    selectedFiles = [];
                    displayFileList();
                    fileInput.value = '';
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Fehler beim Upload: ' + error.message, 'error');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Hochladen und Importieren';
            }
        }

        // IMAP Import Funktion
        async function importFromImap() {
            const folder = document.getElementById('imap-folder').value;
            const limit = document.getElementById('imap-limit').value;
            const unreadOnly = document.getElementById('imap-unread').checked;

            try {
                const response = await fetch('{{ route("emails.import.imap") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        folder,
                        limit: limit ? parseInt(limit) : null,
                        unread_only: unreadOnly
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Fehler beim Import: ' + error.message, 'error');
            }
        }

        // Datumsbereich Import
        async function importByDateRange() {
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;

            if (!dateFrom || !dateTo) {
                showMessage('Bitte beide Daten auswählen', 'error');
                return;
            }

            try {
                const response = await fetch('{{ route("emails.import.daterange") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        from: dateFrom,
                        to: dateTo,
                        folder: document.getElementById('imap-folder').value
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showMessage(result.message, 'error');
                }
            } catch (error) {
                showMessage('Fehler beim Import: ' + error.message, 'error');
            }
        }

        // Status Message anzeigen
        function showMessage(message, type) {
            const statusDiv = document.getElementById('status-message');
            statusDiv.className = type === 'success' 
                ? 'rounded-lg p-4 mb-8 bg-green-100 text-green-800 border border-green-200'
                : 'rounded-lg p-4 mb-8 bg-red-100 text-red-800 border border-red-200';
            statusDiv.textContent = message;
            statusDiv.classList.remove('hidden');

            setTimeout(() => {
                statusDiv.classList.add('hidden');
            }, 5000);
        }
    </script>
</body>
</html>
