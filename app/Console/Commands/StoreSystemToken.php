<?php

namespace App\Console\Commands;

use App\Models\System;
use Illuminate\Console\Command;

class StoreSystemToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:store-token {--key=SYSTEM_API_TOKEN : The environment variable key to read from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store the system API token from environment variables to the database (hashed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $envKey = $this->option('key');
        $token = env($envKey);

        if (!$token) {
            $this->error("No token found in environment variable: {$envKey}");
            return 1;
        }

        try {
            System::setToken(
                'api_token',
                $token,
                'System API token for external services (hashed)'
            );

            $this->info('System API token has been stored successfully (hashed)!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to store system token: ' . $e->getMessage());
            return 1;
        }
    }
}
