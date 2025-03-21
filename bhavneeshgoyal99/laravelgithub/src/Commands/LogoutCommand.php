<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;

class LogoutCommand extends Command
{
    protected $signature = 'logout';
    protected $description = 'Logout from GitHub (by clearing the stored token)';

    public function handle()
    {
        $envFile = base_path('.env');
        
        if (!file_exists($envFile)) {
            $this->error('.env file not found.');
            return;
        }
    
        // Remove GitHub credentials from the .env file
        $envContent = file_get_contents($envFile);
        $envContent = preg_replace('/^GITHUB_USERNAME=.*$/m', '', $envContent);
        $envContent = preg_replace('/^GITHUB_TOKEN=.*$/m', '', $envContent);
    
        file_put_contents($envFile, $envContent);
    
        // Clear the Laravel config cache
        \Artisan::call('config:clear');
        \Artisan::call('cache:clear');
    
        $this->info('You have logged out of GitHub. Credentials have been removed from .env and cache.');
    }
    
}
