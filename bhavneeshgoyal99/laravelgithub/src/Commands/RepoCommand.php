<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class RepoCommand extends Command
{
    protected $signature = 'repo';
    protected $description = 'Fetch all GitHub repositories for the authenticated user';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $username = env('GITHUB_USERNAME');
        $token = env('GITHUB_TOKEN');

        if (!$username || !$token) {
            $this->error('GitHub username or token is not set in .env file.');
            return;
        }

        // Set up the HTTP client
        try {
            $client = new Client();
            $response = $client->get('https://api.github.com/user/repos', [
                'auth' => [$username, $token]
            ]);

            // Check for successful response
            if ($response->getStatusCode() === 200) {
                $repositories = json_decode($response->getBody(), true);

                if (count($repositories) === 0) {
                    $this->info('No repositories found.');
                } else {
                    $this->info('Repositories:');
                    foreach ($repositories as $repo) {
                        $this->info($repo['full_name']);
                    }
                }
            } else {
                $this->error('Failed to fetch repositories. Response: ' . $response->getBody());
            }
        } catch (\Exception $e) {
            $this->error('Error connecting to GitHub: ' . $e->getMessage());
        }
    }
}
