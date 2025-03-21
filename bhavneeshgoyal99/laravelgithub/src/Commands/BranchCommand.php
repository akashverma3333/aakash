<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class BranchCommand extends Command
{
    protected $signature = 'branch {repo}';
    protected $description = 'Fetch branches of a repository from GitHub using the GitHub API';

    public function handle()
    {
        $repo = $this->argument('repo');
        $token = env('GITHUB_TOKEN');
        $client = new Client();

        try {
            $response = $client->get("https://api.github.com/repos/{$repo}/branches", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ]
            ]);

            $branches = json_decode($response->getBody(), true);
            foreach ($branches as $branch) {
                $this->info($branch['name']);
            }
        } catch (\Exception $e) {
            $this->error("Failed to fetch branches: " . $e->getMessage());
        }
    }
}
