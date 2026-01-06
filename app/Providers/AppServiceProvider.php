<?php

namespace App\Providers;

use App\Models\Attachment;
use App\Models\Bug;
use App\Models\Comment;
use App\Policies\AttachmentPolicy;
use App\Policies\BugPolicy;
use App\Policies\CommentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::policy(Bug::class, BugPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Attachment::class, AttachmentPolicy::class);
    }
}
