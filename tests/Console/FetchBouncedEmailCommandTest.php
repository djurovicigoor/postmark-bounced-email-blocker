<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\Console;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\TestCase;

class FetchBouncedEmailCommandTest extends TestCase {
	
	/** @test */
	public function it_creates_the_file() {
		
		$this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;
		
		$this->postmarkBouncedEmails()->setStoragePath($this->storagePath);
		
		$this->assertFileDoesNotExist($this->storagePath);
		
		$this->artisan('postmark-bounced-email:fetch')
			->assertExitCode(0);
		
		$this->assertFileExists($this->storagePath);
		
		$emails = $this->postmarkBouncedEmails()->getEmails();
		
		$this->assertIsArray($emails);
		$this->assertContains(env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL'), $emails);
	}
	
	/** @test */
	public function it_overwrites_the_file() {
		
		file_put_contents($this->storagePath, json_encode(['foo-bar']));
		
		$this->artisan('postmark-bounced-email:fetch')
			->assertExitCode(0);
		
		$this->assertFileExists($this->storagePath);
		
		$emails = $this->postmarkBouncedEmails()->getEmails();
		
		$this->assertIsArray($emails);
		$this->assertContains(env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL'), $emails);
		$this->assertNotContains('foo-bar', $emails);
	}
}