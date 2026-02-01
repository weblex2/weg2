<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $email->subject ?: '(Kein Betreff)' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-4">
            <a href="{{ route('emails.import') }}" class="text-blue-600 hover:underline">← Zurück zur Übersicht</a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- E-Mail Header -->
            <div class="bg-gray-50 border-b px-6 py-4">
                <h1 class="text-2xl font-bold mb-4">{{ $email->subject ?: '(Kein Betreff)' }}</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-semibold">Von:</span>
                        <div class="ml-4">
                            @if ($email->from_name)
                                <div>{{ $email->from_name }}</div>
                                <div class="text-gray-600">&lt;{{ $email->from_email }}&gt;</div>
                            @else
                                <div>{{ $email->from_email }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="font-semibold">Datum:</span>
                        <div class="ml-4">{{ $email->email_date->format('d.m.Y H:i:s') }}</div>
                    </div>

                    @if ($email->to && count($email->to) > 0)
                        <div class="md:col-span-2">
                            <span class="font-semibold">An:</span>
                            <div class="ml-4">
                                @foreach ($email->to as $recipient)
                                    <div>
                                        @if (isset($recipient['name']) && $recipient['name'])
                                            {{ $recipient['name'] }} &lt;{{ $recipient['email'] }}&gt;
                                        @else
                                            {{ $recipient['email'] }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($email->cc && count($email->cc) > 0)
                        <div class="md:col-span-2">
                            <span class="font-semibold">CC:</span>
                            <div class="ml-4">
                                @foreach ($email->cc as $recipient)
                                    <div>
                                        @if (isset($recipient['name']) && $recipient['name'])
                                            {{ $recipient['name'] }} &lt;{{ $recipient['email'] }}&gt;
                                        @else
                                            {{ $recipient['email'] }}
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <span class="font-semibold">Quelle:</span>
                        <span
                            class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                     {{ $email->source === 'imap' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ strtoupper($email->source) }}
                        </span>
                    </div>

                    @if ($email->message_id)
                        <div class="md:col-span-2">
                            <span class="font-semibold">Message-ID:</span>
                            <div class="ml-4 text-xs text-gray-600 break-all">{{ $email->message_id }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Anhänge -->
            @if ($email->has_attachments && $email->attachments->count() > 0)
                <div class="border-b px-6 py-4 bg-yellow-50">
                    <h2 class="font-semibold mb-2">📎 Anhänge ({{ $email->attachments->count() }})</h2>
                    <div class="space-y-2">
                        @foreach ($email->attachments as $attachment)
                            <div class="flex items-center justify-between bg-white p-2 rounded border">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                        </path>
                                    </svg>
                                    <div>
                                        <div class="font-medium">{{ $attachment->filename }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $attachment->formatted_size }} - {{ $attachment->mime_type }}
                                        </div>
                                    </div>
                                </div>
                                <a href="{{ $attachment->url }}" target="_blank"
                                    class="text-blue-600 hover:text-blue-800 px-3 py-1 border border-blue-600 rounded hover:bg-blue-50">
                                    Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- E-Mail Body -->
            <div class="px-6 py-4">
                <!-- Tab Navigation -->
                <div class="flex border-b mb-4">
                    @if ($email->html_body)
                        <button onclick="showTab('html')" id="tab-html"
                            class="px-4 py-2 font-medium border-b-2 border-blue-600 text-blue-600">
                            HTML
                        </button>
                    @endif
                    @if ($email->text_body)
                        <button onclick="showTab('text')" id="tab-text"
                            class="px-4 py-2 font-medium {{ $email->html_body ? 'text-gray-600' : 'border-b-2 border-blue-600 text-blue-600' }}">
                            Text
                        </button>
                    @endif
                    <button onclick="showTab('headers')" id="tab-headers" class="px-4 py-2 font-medium text-gray-600">
                        Headers
                    </button>
                </div>

                <!-- HTML View -->
                @if ($email->html_body)
                    <div id="content-html" class="{{ $email->html_body ? '' : 'hidden' }}">
                        <iframe srcdoc="{{ htmlspecialchars($email->html_body) }}" class="w-full border rounded"
                            style="min-height: 500px;" sandbox="allow-same-origin"></iframe>
                    </div>
                @endif

                <!-- Text View -->
                @if ($email->text_body)
                    <div id="content-text" class="{{ $email->html_body ? 'hidden' : '' }}">
                        <pre class="whitespace-pre-wrap bg-gray-50 p-4 rounded border text-sm">{{ $email->text_body }}</pre>
                    </div>
                @endif

                <!-- Headers View -->
                <div id="content-headers" class="hidden">
                    @if ($email->headers && count($email->headers) > 0)
                        <div class="bg-gray-50 p-4 rounded border">
                            <table class="min-w-full text-sm">
                                @foreach ($email->headers as $key => $value)
                                    <tr class="border-b">
                                        <td class="py-2 pr-4 font-semibold align-top">{{ $key }}:</td>
                                        <td class="py-2 break-all">
                                            @if (is_array($value))
                                                {{ implode(', ', $value) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">Keine Header-Informationen verfügbar</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            // Hide all content
            document.querySelectorAll('[id^="content-"]').forEach(el => {
                el.classList.add('hidden');
            });

            // Remove active state from all tabs
            document.querySelectorAll('[id^="tab-"]').forEach(el => {
                el.classList.remove('border-blue-600', 'text-blue-600');
                el.classList.add('text-gray-600');
            });

            // Show selected content
            document.getElementById('content-' + tab).classList.remove('hidden');

            // Activate selected tab
            const activeTab = document.getElementById('tab-' + tab);
            activeTab.classList.add('border-blue-600', 'text-blue-600');
            activeTab.classList.remove('text-gray-600');
        }
    </script>
</body>

</html>
