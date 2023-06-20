<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Console;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Contracts\Fetcher;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Fetcher\EmailFetcher;
use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository as Config;

class FetchPostmarkBouncedEmailsCommand extends Command {
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'postmark-bounced-email:fetch';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Updates to the latest bounced emails list.';
	
	/**
	 * Execute the console command.
	 *
	 * @param Config                      $config
	 * @param PostmarkBouncedEmailBlocker $postmarkBouncedEmailBlocker
	 *
	 * @return  int
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 * @throws \Psr\SimpleCache\InvalidArgumentException
	 */
	public function handle(Config $config, PostmarkBouncedEmailBlocker $postmarkBouncedEmailBlocker): int {
		
		$this->line('Fetching from source...');
		
		$fetcher = $this->laravel->make($fetcherClass = EmailFetcher::class);
		
		if (!$fetcher instanceof Fetcher) {
			$this->error($fetcherClass . ' should implement ' . Fetcher::class);
			
			return 1;
		}
		
		$data = $this->laravel->call([$fetcher, 'handle'], [
			'postmarkServerApiToken' => $config->get('postmark-bounced-email-blocker.server-api-token'),
		]);
		
		$this->line('Saving response to storage...');
		
		if ($postmarkBouncedEmailBlocker->saveToStorage($data)) {
			$this->info('Bounced emails list updated successfully.');
			$postmarkBouncedEmailBlocker->bootstrap();
			
			return 0;
		} else {
			$this->error('Could not write to storage (' . $postmarkBouncedEmailBlocker->getStoragePath() . ')!');
			
			return 1;
		}
	}
}