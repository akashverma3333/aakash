<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EditBranchFileCommand extends Command
{
    protected $signature = 'edit-branch-file {repo} {branch} {file}';
    protected $description = 'Fetch a file from a GitHub branch and open it in VS Code for editing';

    public function handle()
    {
        $repo = $this->argument('repo'); // Example: "akashverma3333/laravelgithubapis"
        $branch = $this->argument('branch'); // Example: "main"
        $file = $this->argument('file'); // Example: "src/Commands/RepoCommand.php"
        $token = env('GITHUB_TOKEN');

        if (!$token) {
            $this->error('GitHub token is missing! Set GITHUB_TOKEN in .env');
            return 1;
        }

        // Fetch file content
        $url = "https://raw.githubusercontent.com/$repo/$branch/$file";
        $response = Http::withToken($token)->get($url);

        if ($response->failed()) {
            $this->error("Failed to fetch file '$file' from branch '$branch'.");
            return 1;
        }

        $content = $response->body();

        // Save to a temporary directory
        $localPath = storage_path("github_files/$file");
        $directory = dirname($localPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($localPath, $content);

        // Open file in VS Code
        $this->info("Opening file '$file' in VS Code...");
        exec("code $localPath");

        return 0;
    }
}
