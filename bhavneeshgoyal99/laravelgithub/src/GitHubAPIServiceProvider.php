<?php

namespace Bhavneeshgoyal99\LaravelGitHub;

use Illuminate\Support\ServiceProvider;

class GitHubAPIServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register any bindings or services if needed
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) { // Ensure commands only register in CLI
            $this->commands([
                \Bhavneeshgoyal99\LaravelGitHub\Commands\RepoCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\BranchCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\LoginCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\LogoutCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\CheckoutBranchCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\EditBranchFileCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\ShowGitHelpCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\ShowBranchContentCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\CreatePullRequestCommand::class,
                \Bhavneeshgoyal99\LaravelGitHub\Commands\CreateBranchCommand::class,
                
            ]);
        }
    }
}
