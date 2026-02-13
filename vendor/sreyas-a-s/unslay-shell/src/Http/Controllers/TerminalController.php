<?php

namespace SreyasAS\UnSlayShell\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TerminalController extends Controller
{
    public function index()
    {
        return view('unslay-shell::index');
    }

    public function login(Request $request)
    {
        $password = config('unslay-shell.password', 'admin');

        if ($request->input('password') === $password) {
            session(['terminal_authenticated' => true]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid password']);
    }

    public function execute(Request $request)
    {
        // Check authentication
        if (!session('terminal_authenticated')) {
            return response()->json(['output' => 'Unauthorized. Please refresh and log in.']);
        }

        $command = $request->input('command');
        $cwd = $request->input('cwd', base_path());

        if (empty($command)) {
            return response()->json(['output' => '']);
        }

        $trimmedCommand = trim($command);

        // Handle built-in commands
        if ($trimmedCommand === 'logout' || $trimmedCommand === 'exit') {
            session()->forget('terminal_authenticated');
            return response()->json(['output' => 'Logging out...', 'logout' => true]);
        }

        if ($trimmedCommand === 'clear') {
            return response()->json(['output' => '', 'clear' => true]);
        }

        if ($trimmedCommand === 'cd' || str_starts_with($trimmedCommand, 'cd ')) {
            $path = trim(substr($trimmedCommand, 2));

            if (empty($path)) {
                $newCwd = base_path();
            } elseif ($path === '..') {
                $newCwd = dirname($cwd);
            } else {
                // Handle relative paths
                if (!str_starts_with($path, '/') && !str_starts_with($path, '\\') && !str_contains($path, ':')) {
                    $newCwd = $cwd . DIRECTORY_SEPARATOR . $path;
                } else {
                    $newCwd = $path;
                }
            }

            if (is_dir($newCwd)) {
                return response()->json([
                    'output' => '',
                    'cwd' => realpath($newCwd)
                ]);
            } else {
                return response()->json([
                    'output' => "bash: cd: $path: No such file or directory\n",
                    'cwd' => $cwd
                ]);
            }
        }

        // Prepare command
        if (stripos($trimmedCommand, 'php') === 0) {
            $command = preg_replace('/^php\b/i', '"' . PHP_BINARY . '"', $trimmedCommand);
        }

        // Prepare Environment
        $env = getenv();
        if (PHP_OS_FAMILY === 'Windows') {
            $env['SystemRoot'] = getenv('SystemRoot');
            $env['ANSICON'] = '120x80';
        }
        $env['PATH'] = getenv('PATH');
        $env['FORCE_COLOR'] = '1';
        $env['TERM'] = 'xterm-256color';

        $defaultConnection = config('database.default');
        $dbConfig = config("database.connections.{$defaultConnection}");

        if ($dbConfig) {
            $env['DB_CONNECTION'] = $defaultConnection;
            $env['DB_HOST'] = $dbConfig['host'] ?? '127.0.0.1';
            $env['DB_PORT'] = $dbConfig['port'] ?? '3306';
            $env['DB_DATABASE'] = $dbConfig['database'] ?? '';
            $env['DB_USERNAME'] = $dbConfig['username'] ?? 'root';
            $env['DB_PASSWORD'] = $dbConfig['password'] ?? '';
        }

        // Return Streamed Response
        return response()->stream(function () use ($command, $cwd, $env) {
            try {
                $process = Process::path($cwd)->env($env)->start($command, function ($type, $output) {
                    echo json_encode(['output' => $output]) . "\n";
                    if (ob_get_level() > 0)
                        ob_flush();
                    flush();
                });

                $process->wait();
            } catch (\Exception $e) {
                echo json_encode(['output' => "\nError: " . $e->getMessage()]) . "\n";
                if (ob_get_level() > 0)
                    ob_flush();
                flush();
            }
        }, 200, [
            'Content-Type' => 'application/x-ndjson',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
    public function autocomplete(Request $request)
    {
        // Check authentication
        if (!session('terminal_authenticated')) {
            return response()->json(['matches' => []]);
        }

        $command = $request->input('command');
        $cwd = $request->input('cwd', base_path());

        // Basic implementation: Complete files in the current directory
        // In a real shell, this would vary based on cursor position and context

        $parts = explode(' ', $command);
        $lastPart = end($parts);

        // If the last part is empty, we list everything (or nothing, depending on UX preference)
        // Let's assume we match against files in CWD

        $search = $cwd . DIRECTORY_SEPARATOR . $lastPart . '*';
        $matches = glob($search);

        $results = [];
        if ($matches) {
            foreach ($matches as $match) {
                // Return path relative to CWD
                $rel = substr($match, strlen($cwd));
                if (str_starts_with($rel, DIRECTORY_SEPARATOR) || str_starts_with($rel, '/') || str_starts_with($rel, '\\')) {
                    $rel = substr($rel, 1);
                }

                if (is_dir($match)) {
                    $rel .= DIRECTORY_SEPARATOR;
                }
                $results[] = $rel;
            }
        }

        // Add basic commands if we are at the start
        if (count($parts) <= 1) {
            $basicCommands = ['cd', 'clear', 'ls', 'php', 'artisan', 'composer', 'npm', 'git', 'logout', 'exit'];
            foreach ($basicCommands as $cmd) {
                if (str_starts_with($cmd, $lastPart)) {
                    $results[] = $cmd;
                }
            }
        }

        return response()->json(['matches' => array_values(array_unique($results))]);
    }
}
