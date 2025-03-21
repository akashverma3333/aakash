<?php

namespace BhavneeshGoyal99\LaravelGithub\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class CreatePullRequestCommand extends Command
{
    protected $signature = 'github:create-pull-request';
    protected $description = 'Interactively create a GitHub pull request with a predefined template.';

    public function handle()
    {
        $githubToken = env('GITHUB_TOKEN');
        if (!$githubToken) {
            return $this->error("GitHub token not set. Please add GITHUB_TOKEN to .env");
        }

        // Get repository details
        $repo = $this->ask('Enter your GitHub repository (username/repo-name)', $this->getRepository($githubToken));
        if (!$repo) {
            return $this->error("Failed to detect the GitHub repository.");
        }

        // Get user input
        $ticketId = $this->ask('Enter Ticket ID');
        $title = $this->ask('Enter PR Title');
        $description = $this->ask('Enter Description');
        $featureBranch = $this->ask('Enter Feature Branch');
        $baseBranch = $this->ask('Enter Base Branch (default: main)', 'main');

        $username = $this->getGitHubUsername($githubToken);
        $url = "https://github.com/$repo/compare/$baseBranch...$featureBranch";

        // Validate feature branch existence
        if (!$this->branchExists($repo, $featureBranch, $githubToken)) {
            $this->warn("Branch '$featureBranch' does not exist. Creating it...");
            if (!$this->createBranchOnGitHub($repo, $featureBranch, $baseBranch, $githubToken)) {
                return $this->error("Failed to create feature branch.");
            }
        }

        // Fetch default assignees and reviewers
        $assignees = $this->getDefaultAssignees($repo, $githubToken);
        $reviewers = $this->getDefaultReviewers($repo, $githubToken);

        // Load PR template
        $prBody = $this->loadPrTemplate($ticketId, $title, $description, $url, $username, $featureBranch);
        if (!$prBody) {
            return $this->error("Failed to load PR template.");
        }

        // Create Pull Request
        $payload = [
            'title' => "[$ticketId] - $title",
            'head' => $featureBranch,
            'base' => $baseBranch,
            'body' => $prBody,
            'assignees' => $assignees,
            'reviewers' => $reviewers,
        ];

        $response = $this->postToGitHub("repos/$repo/pulls", $githubToken, $payload);

        if ($response->successful()) {
            $prUrl = $response->json()['html_url'];
            $this->info("✅ Pull request created successfully: $prUrl");

            if (!empty($reviewers)) {
                $this->assignReviewers($repo, $prUrl, $reviewers, $githubToken);
            }
        } else {
            $this->error("❌ Failed to create pull request: " . $response->body());
        }
    }

    /**
     * Load the PR template and replace placeholders.
     */
    private function loadPrTemplate($ticketId, $title, $description, $url, $username, $featureBranch)
    {
        return <<<EOT
**PR Title:** [#{$ticketId}] - {$title}

**Description:**
{$description}

**Feature Branch:** {$featureBranch}

**PR URL:** {$url}

**Submitted by:** {$username}
EOT;
    }

    /**
     * Assign reviewers to the created PR.
     */
    private function assignReviewers($repo, $prUrl, $reviewers, $githubToken)
    {
        preg_match('/pull\/(\d+)/', $prUrl, $matches);
        if (!isset($matches[1])) {
            return $this->error("❌ Could not extract PR number from URL.");
        }

        $prNumber = $matches[1];
        $response = $this->postToGitHub("repos/$repo/pulls/$prNumber/requested_reviewers", $githubToken, [
            'reviewers' => $reviewers
        ]);

        $response->successful()
            ? $this->info("✅ Reviewers assigned successfully.")
            : $this->error("❌ Failed to assign reviewers: " . $response->body());
    }

    /**
     * Get the GitHub username of the authenticated user.
     */
    private function getGitHubUsername($githubToken)
    {
        $response = $this->getFromGitHub("user", $githubToken);
        return $response->successful() ? $response->json()['login'] : 'unknown_user';
    }

    /**
     * Get default assignees from the repository collaborators.
     */
    private function getDefaultAssignees($repo, $githubToken)
    {
        $response = $this->getFromGitHub("repos/$repo/collaborators", $githubToken);
        return $response->successful() ? array_column($response->json(), 'login') : [];
    }

    /**
     * Get default reviewers dynamically.
     */
    private function getDefaultReviewers($repo, $githubToken)
    {
        $response = $this->getFromGitHub("repos/$repo/teams", $githubToken);
        return $response->successful() ? array_column($response->json(), 'slug') : [];
    }

    /**
     * Retrieve user repositories and allow selection.
     */
    private function getRepository($githubToken)
    {
        $response = $this->getFromGitHub("user/repos", $githubToken);
        if ($response->successful()) {
            $repositories = $response->json();
            if (!empty($repositories)) {
                $repoNames = array_map(fn($repo) => $repo['full_name'], $repositories);
                return $this->choice('Select your GitHub repository:', $repoNames, 0);
            }
        }
        return null;
    }

    /**
     * Check if a branch exists in the repository.
     */
    private function branchExists($repo, $branch, $githubToken)
    {
        return $this->getFromGitHub("repos/$repo/git/refs/heads/$branch", $githubToken)->successful();
    }

    /**
     * Create a new branch on GitHub.
     */
    private function createBranchOnGitHub($repo, $featureBranch, $baseBranch, $githubToken)
    {
        $response = $this->getFromGitHub("repos/$repo/git/refs/heads/$baseBranch", $githubToken);
        if (!$response->successful()) {
            return $this->error("Failed to fetch base branch '$baseBranch'.");
        }

        $latestCommitSha = $response->json()['object']['sha'];

        $createResponse = $this->postToGitHub("repos/$repo/git/refs", $githubToken, [
            'ref' => "refs/heads/$featureBranch",
            'sha' => $latestCommitSha
        ]);

        return $createResponse->successful();
    }

    /**
     * Generic method for GET requests to GitHub API.
     */
    private function getFromGitHub($endpoint, $githubToken)
    {
        return Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "Bearer $githubToken"
        ])->get("https://api.github.com/$endpoint");
    }

    /**
     * Generic method for POST requests to GitHub API.
     */
    private function postToGitHub($endpoint, $githubToken, $data)
    {
        return Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "Bearer $githubToken"
        ])->post("https://api.github.com/$endpoint", $data);
    }
}
