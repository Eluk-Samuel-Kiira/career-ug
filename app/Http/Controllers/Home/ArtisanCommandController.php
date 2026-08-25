<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{ Artisan, Auth };
use Illuminate\Support\Facades\Log;

class ArtisanCommandController extends Controller
{
    // Whitelist of allowed commands for security
    protected $allowedCommands = [
        'storage:link'         => 'Create storage symlink',
        'cache:clear'          => 'Clear application cache',
        'config:clear'         => 'Clear config cache',
        'config:cache'         => 'Cache configuration',
        'route:clear'          => 'Clear route cache',
        'route:cache'          => 'Cache routes',
        'view:clear'           => 'Clear compiled views',
        'optimize:clear'       => 'Clear all cached data',
        'optimize'             => 'Cache config, routes & views',
        'queue:restart'        => 'Restart queue workers',
        // 'migrate'              => 'Run database migrations (forced in production)',
        'migrate:status'       => 'Show migration status',
        // 'db:seed'              => 'Seed the database',
    ];

    // Commands that automatically get --force in production
    protected $forceInProduction = [
        'migrate',
        'migrate:fresh --seed',
        'db:seed',
    ];

    public function index()
    {
        return view('home.artisan', [
            'commands' => $this->allowedCommands,
        ]);
    }

    public function run(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');
        $actualCommand = $command;

        // Security check — only allow whitelisted commands
        if (!array_key_exists($command, $this->allowedCommands)) {
            return response()->json([
                'success' => false,
                'output'  => '❌ Command not allowed.',
            ], 403);
        }

        // Force flag for specific commands in production
        if (app()->environment('production') && in_array($command, $this->forceInProduction)) {
            if (!str_contains($command, '--force')) {
                $actualCommand = $command . ' --force';
            }
            
            // Log dangerous operations
            if ($command === 'migrate:fresh --seed') {
                Log::warning('⚠️ DANGEROUS: migrate:fresh --seed triggered', [
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            }
        }

        try {
            Artisan::call($actualCommand);
            $output = Artisan::output();

            $message = $output ?: '✅ Command executed successfully with no output.';
            
            if ($actualCommand !== $command) {
                $message = "⚠️ Production mode: Added --force flag.\n\n" . $message;
            }

            return response()->json([
                'success' => true,
                'command' => 'php artisan ' . $actualCommand,
                'output'  => $message,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'command' => 'php artisan ' . ($actualCommand ?? $command),
                'output'  => '❌ Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}