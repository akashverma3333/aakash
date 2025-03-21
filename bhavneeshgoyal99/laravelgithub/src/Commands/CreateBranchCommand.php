<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CreateBranchCommand extends Command
{
    protected $signature = 'create-branch {repo} {new_branch} {base_branch}';
    protected $description = 'Create a new branch on GitHub using the API';

    public function handle()
    {
        $repo = $this->argument('repo'); // Example: "akashverma3333/laravelgithubapis"
        $newBranch = $this->argument('new_branch'); // New branch name
        $baseBranch = $this->argument('base_branch'); // Base branch name
        $token = env('GITHUB_TOKEN'); // GitHub API token from .env

        if (!$token) {
            $this->error('GitHub token is missing! Set GITHUB_TOKEN in .env');
            return 1;
        }

        // Step 1: Get the latest commit SHA of the base branch
        $branchResponse = Http::withToken($token)
            ->get("https://api.github.com/repos/$repo/git/ref/heads/$baseBranch");

        if ($branchResponse->failed()) {
            $this->error("Base branch '$baseBranch' not found in repository '$repo'.");
            return 1;
        }

        $latestCommitSHA = $branchResponse->json()['object']['sha'];

        // Step 2: Create the new branch
        $createBranchResponse = Http::withToken($token)
            ->post("https://api.github.com/repos/$repo/git/refs", [
                'ref' => "refs/heads/$newBranch",
                'sha' => $latestCommitSHA
            ]);

        if ($createBranchResponse->successful()) {
            $this->info("Branch '$newBranch' created successfully in '$repo'.");
            return 0;
        } else {
            $this->error("Failed to create branch. " . $createBranchResponse->body());
            return 1;
        }
    }
}
