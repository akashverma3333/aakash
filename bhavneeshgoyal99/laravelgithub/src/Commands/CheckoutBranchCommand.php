<?php

namespace Bhavneeshgoyal99\LaravelGitHub\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class CheckoutBranchCommand extends Command
{
    protected $signature = 'branch:checkout {repository} {branch}';
    protected $description = 'Checkout to a branch using GitHub API or local Git';

    public function handle()
    {
        // ✅ Check if Git is installed before proceeding
        if (!$this->isGitInstalled()) {
            return;
        }

        $repository = $this->argument('repository');
        $branch = $this->argument('branch');

        // Your logic to checkout the branch
        $this->checkoutBranch($repository, $branch);
    }

    // ✅ Check if Git is installed
    private function isGitInstalled(): bool
    {
        $process = new Process(['git', '--version']);
        $process->run();

        if ($process->isSuccessful()) {
            $this->info("✅ Git is already installed on your system.");
            return true;
        }

        $this->error("❌ Git is not installed.");
        if ($this->confirm("Do you want to install Git now?")) {
            $this->installGit();
        }

        return false;
    }

    // ✅ Install Git automatically
    private function installGit()
    {
        $this->info("Installing Git...");
    
        if (PHP_OS_FAMILY === 'Windows') {
            $installerUrl = "https://git-scm.com/download/win";
    
            $this->warn("Automatic installation is not possible. Please install Git manually from: $installerUrl");
            return;
        }
    
        // For Linux/macOS
        $process = new Process(['sudo', 'apt-get', 'install', '-y', 'git']);
        $process->run();
    
        if ($process->isSuccessful()) {
            $this->info("✅ Git installed successfully! Please restart your terminal and try again.");
        } else {
            $this->error("❌ Failed to install Git. Please install it manually.");
        }
    }
    

    // ✅ Checkout branch logic (modify as needed)
    private function checkoutBranch($repo, $branch)
    {
        $repoName = basename($repo); 
        $repoPath = base_path($repoName);
    
        // 🔍 Check if the repository folder exists
        if (!is_dir($repoPath)) {
            $this->error("❌ Repository folder '$repoPath' not found.");
            
            // 🔄 Ask the user if they want to clone it
            if ($this->confirm("Do you want to clone '$repo' now?")) {
                $this->cloneRepository($repo);
            } else {
                return;
            }
        }
    
        // 🔄 Attempt to checkout the branch
        $process = new Process(['git', 'checkout', $branch], $repoPath);
        $process->run();
    
        if ($process->isSuccessful()) {
            $this->info("✅ Successfully switched to branch: $branch");
        } else {
            $this->error("❌ Failed to checkout to branch: $branch. Make sure it exists.");
        }
    }
    private function cloneRepository($repo)
{
    $repoName = basename($repo);
    $repoURL = "https://github.com/$repo.git";
    $repoPath = base_path($repoName);

    $this->info("🔄 Cloning repository: $repoURL ...");

    $process = new Process(['git', 'clone', $repoURL, $repoPath]);
    $process->run();

    if ($process->isSuccessful()) {
        $this->info("✅ Repository '$repo' cloned successfully!");
    } else {
        $this->error("❌ Failed to clone repository '$repo'. Check your internet connection or repo access.");
    }
}

    
}
