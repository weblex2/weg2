<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeployController extends Controller
{
    public function index()
    {
        return view('deploy');
    }

   public function deploy(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        if ($request->token !== env('DEPLOY_TOKEN')) {
            return back()->with('error', 'Ungültiger Token!');
        }

        $output = [];
        $result = 0;

        $basePath = base_path();
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($isWindows) {
            $command = "cd /d {$basePath} && git pull origin master 2>&1";
            exec($command, $output, $result);
        } else {
            // Kompletter Pfad zum Script
            $scriptPath = $basePath . '/deploy.sh';
            $command = "bash {$scriptPath} 2>&1";
            exec($command, $output, $result);
        }

        $message = $result === 0 ? 'Deployment erfolgreich!' : 'Deployment fehlgeschlagen!';

        return back()->with([
            'status' => $result === 0 ? 'success' : 'error',
            'message' => $message,
            'output' => implode("\n", $output)
        ]);
    }
}
