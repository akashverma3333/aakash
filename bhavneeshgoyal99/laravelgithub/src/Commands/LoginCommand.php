<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class LoginCommand extends Command
{
    protected $signature = 'login';
    protected $description = 'Login to GitHub and store credentials in .env file';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Check if credentials already exist in .env
        $existingUsername = env('GITHUB_USERNAME');
        $existingToken = env('GITHUB_TOKEN');

        if ($existingUsername && $existingToken) {
            $this->info('You are already logged in as ' . $existingUsername);
            return;
        }

        // Ask for GitHub username and token
        $username = $this->ask('Enter your GitHub username');
        $token = $this->secret('Enter your GitHub personal access token');

        if (!$username || !$token) {
            $this->error('GitHub username or token cannot be empty.');
            return;
        }

        // Verify the GitHub token
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/user', [
                'auth' => [$username, $token]
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->error('Invalid GitHub token.');
                return;
            }

            // Store credentials in the .env file
            $this->updateEnvFile($username, $token);

            // **Manually reload environment variables**
            $this->reloadEnv();

            $this->info('Logged in successfully as ' . $username);
        } catch (\Exception $e) {
            $this->error('Error connecting to GitHub: ' . $e->getMessage());
        }
    }

    private function updateEnvFile($username, $token)
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            $this->error('.env file not found.');
            return;
        }

        // Read the existing .env file
        $envContent = file_get_contents($envFile);

        // Remove old credentials if they exist
        $envContent = preg_replace('/^GITHUB_USERNAME=.*/m', '', $envContent);
        $envContent = preg_replace('/^GITHUB_TOKEN=.*/m', '', $envContent);

        // Append new credentials
        file_put_contents($envFile, $envContent . "\nGITHUB_USERNAME={$username}\nGITHUB_TOKEN={$token}\n");
    }

    private function reloadEnv()
    {
        // **Clear Laravel configuration cache**
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        // **Reload environment variables manually**
        $dotenv = \Dotenv\Dotenv::createImmutable(base_path());
        $dotenv->load();

        // **Update Laravel config in runtime**
        Config::set('github.username', env('GITHUB_USERNAME'));
        Config::set('github.token', env('GITHUB_TOKEN'));
    }
}
