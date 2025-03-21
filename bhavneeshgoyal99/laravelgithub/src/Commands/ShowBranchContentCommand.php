<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ShowBranchContentCommand extends Command
{
    protected $signature = 'show-branch {repo} {branch}';
    protected $description = 'Display the contents of the main branch in a GitHub repository';

    public function handle()
    {
        $repo = $this->argument('repo'); // Example: "akashverma3333/laravelgithubapis"
        $branch = $this->argument('branch'); // Example: "main"
        $token = env('GITHUB_TOKEN'); // GitHub API Token

        if (!$token) {
            $this->error('GitHub token is missing! Set GITHUB_TOKEN in .env');
            return 1;
        }

        // Fetch the contents of the branch
        $response = Http::withToken($token)
            ->get("https://api.github.com/repos/$repo/git/trees/$branch?recursive=1");

        if ($response->failed()) {
            $this->error("Failed to fetch branch contents for '$branch'.");
            return 1;
        }

        $tree = $response->json()['tree'] ?? [];

        if (empty($tree)) {
            $this->info("The branch '$branch' is empty.");
            return 0;
        }

        $this->info("Contents of branch '$branch' in repository '$repo':");

        foreach ($tree as $item) {
            if ($item['type'] === 'blob') {
                $this->line("- " . $item['path']);
            }
        }

        return 0;
    }
}
