<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests;

use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	
	/**
	 * @var string
	 */
	protected $storagePath = __DIR__ . '/postmark-bounced-emails.json';
	
	/**
	 * Define environment setup.
	 *
	 * @param Application $app
	 *
	 * @return void
	 */
	protected function getEnvironmentSetUp($app) {
	
		// make sure, our .env file is loaded
		$app->useEnvironmentPath(__DIR__.'/..');
		$app->bootstrapWith([LoadEnvironmentVariables::class]);
		
		$app['config']->set('postmark-bounced-email-blocker.server-api-token', env('POSTMARK_BOUNCED_EMAIL_BLOCKER_SERVER_API_TOKEN', NULL));
		
		parent::getEnvironmentSetUp($app);

	}
	
	/**
	 * Setup the test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		
		parent::setUp();
		
		$this->postmarkBouncedEmails()->flushStorage();
		$this->postmarkBouncedEmails()->flushCache();
	}
	
	/**
	 * Clean up the testing environment before the next test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		
		$this->postmarkBouncedEmails()->flushStorage();
		$this->postmarkBouncedEmails()->flushCache();
		
		parent::tearDown();
	}
	
	/**
	 * Package Service Providers
	 *
	 * @param Application $app
	 *
	 * @return array
	 */
	protected function getPackageProviders($app) {
		
		return ['Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlockerServiceProvider'];
	}
	
	/**
	 * Package Aliases
	 *
	 * @param Application $app
	 *
	 * @return array
	 */
	protected function getPackageAliases($app) {
		
		return ['PostmarkBouncedEmailBlockerFacade' => 'Djurovicigoor\PostmarkBouncedEmailBlocker\Facades\PostmarkBouncedEmailBlockerFacade'];
	}
	
	/**
	 * @return PostmarkBouncedEmailBlocker
	 */
	protected function postmarkBouncedEmails(): PostmarkBouncedEmailBlocker {
		
		return $this->app['postmark_bounced.emails'];
	}
}