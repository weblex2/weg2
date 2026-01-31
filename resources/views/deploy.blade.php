<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deploy</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">🚀 Laravel Deployment22</h1>

        @if (session('status'))
            <div
                class="mb-4 p-4 rounded {{ session('status') === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ session('message') }}
            </div>
        @endif

        @if (session('output'))
            <div class="mb-4 p-4 bg-gray-800 text-green-400 rounded font-mono text-sm overflow-auto">
                <pre>{{ session('output') }}</pre>
            </div>
        @endif

        <form method="POST" action="{{ route('deploy.run') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Deploy Token:</label>
                <input type="password" name="token" required
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Token eingeben">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded transition">
                🚀 Deploy starten
            </button>
        </form>
    </div>
</body>

</html>
