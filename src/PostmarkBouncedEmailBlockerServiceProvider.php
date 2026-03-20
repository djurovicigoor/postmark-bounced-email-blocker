<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Console\FetchPostmarkBouncedEmailsCommand;

class PostmarkBouncedEmailBlockerServiceProvider extends ServiceProvider
{
    /**
     * The config source path.
     */
    protected string $config = __DIR__.'/../config/postmark-bounced-email-blocker.php';

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands(FetchPostmarkBouncedEmailsCommand::class);
        }

        $this->publishes([
            $this->config => config_path('postmark-bounced-email-blocker.php'),
        ], 'postmark-bounced-email-blocker');
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->config, 'postmark-bounced-email-blocker');

        $this->app->singleton('postmark_bounced.emails', function (Application $app): PostmarkBouncedEmailBlocker {
            $cache = null;

            if ($app['config']['postmark-bounced-email-blocker.cache.enabled']) {
                $store = $app['config']['postmark-bounced-email-blocker.cache.store'];
                $cache = $app['cache']->store($store === 'default' ? $app['config']['cache.default'] : $store);
            }

            $instance = new PostmarkBouncedEmailBlocker($cache);

            $instance->setStoragePath($app['config']['postmark-bounced-email-blocker.storage']);
            $instance->setCacheKey($app['config']['postmark-bounced-email-blocker.cache.key']);

            return $instance->bootstrap();
        });

        $this->app->alias('postmark_bounced.emails', PostmarkBouncedEmailBlocker::class);
    }
}
