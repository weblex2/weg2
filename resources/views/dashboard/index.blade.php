@extends('layouts.app')

@section('title', 'Mein Dashboard')

@section('content')
    <h1>🏠 Mein Dashboard</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul style="list-style: none; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="controls">
        <button class="btn" onclick="openModal()">+ Neue Kachel hinzufügen</button>
    </div>

    <div class="grid">
        @forelse($tiles as $tile)
            <div class="tile">
                <a href="{{ $tile->url }}" target="_blank">{{ $tile->name }}</a>
                <div class="tile-actions">
                    <form action="{{ route('tiles.destroy', $tile) }}" method="POST" onsubmit="return confirm('Möchtest du diese Kachel wirklich löschen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Löschen</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; color: white; padding: 40px;">
                <p style="font-size: 1.2rem;">Noch keine Kacheln vorhanden. Füge deine erste Kachel hinzu!</p>
            </div>
        @endforelse
    </div>

    <!-- Modal für neue Kachel -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <h2>Neue Kachel hinzufügen</h2>
            <form action="{{ route('tiles.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="z.B. PhpMyAdmin">
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="url">URL:</label>
                    <input type="url" id="url" name="url" value="{{ old('url') }}" required placeholder="z.B. http://192.168.178.91:8080/">
                    @error('url')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Abbrechen</button>
                    <button type="submit" class="btn">Hinzufügen</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openModal() {
            document.getElementById('addModal').classList.add('active');
            document.getElementById('name').focus();
        }

        function closeModal() {
            document.getElementById('addModal').classList.remove('active');
        }

        // Modal schließen bei Klick außerhalb
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Modal mit ESC schließen
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Alert automatisch ausblenden
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    </script>
@endpush
