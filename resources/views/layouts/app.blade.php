<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .controls {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .btn-danger {
            background: #ff4757;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-danger:hover {
            background: #ee5a6f;
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .tile {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            position: relative;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
        }

        .tile a {
            color: #667eea;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 600;
            display: block;
            margin-bottom: 15px;
        }

        .tile a:hover {
            color: #764ba2;
        }

        .tile-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .tile:hover .tile-actions {
            opacity: 1;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-content h2 {
            margin-bottom: 20px;
            color: #667eea;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .error-message {
            color: #ff4757;
            font-size: 14px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
            }

            .tile {
                padding: 20px;
                min-height: 120px;
            }
        }

        @media (max-width: 480px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
