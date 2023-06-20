<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Console\FetchPostmarkBouncedEmailsCommand;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Validation\BouncedEmailInPostmark;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Factory;

class PostmarkBouncedEmailBlockerServiceProvider extends ServiceProvider {
	
	/**
	 * The config source path.
	 *
	 * @var string
	 */
	protected string $config = __DIR__ . '/../config/postmark-bounced-email-blocker.php';
	
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot(): void {
		
		if ($this->app->runningInConsole()) {
			$this->commands(FetchPostmarkBouncedEmailsCommand::class);
		}
		
		$this->publishes([
			$this->config => config_path('postmark-bounced-email-blocker.php'),
		], 'postmark-bounced-email-blocker');
		
		$this->callAfterResolving('validator', function(Factory $validator) {
			
			$validator->extend('bounced_email_in_postmark', BouncedEmailInPostmark::class . '@validate', BouncedEmailInPostmark::$errorMessage);
		});
	}
	
	public function register() {
		
		$this->mergeConfigFrom($this->config, 'postmark-bounced-email-blocker');
		
		$this->app->singleton('postmark_bounced.emails', function($app) {
			
			// Only build and pass the requested cache store if caching is enabled.
			if ($app['config']['postmark-bounced-email-blocker.cache.enabled']) {
				$store = $app['config']['postmark-bounced-email-blocker.cache.store'];
				$cache = $app['cache']->store($store == 'default' ? $app['config']['cache.default'] : $store);
			}
			
			$instance = new PostmarkBouncedEmailBlocker($cache ?? NULL);
			
			$instance->setStoragePath($app['config']['postmark-bounced-email-blocker.storage']);
			$instance->setCacheKey($app['config']['postmark-bounced-email-blocker.cache.key']);
			
			return $instance->bootstrap();
		});
		
		$this->app->alias('postmark_bounced.emails', PostmarkBouncedEmailBlocker::class);
	}
}