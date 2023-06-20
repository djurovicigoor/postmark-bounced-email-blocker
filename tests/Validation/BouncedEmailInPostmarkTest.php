<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\Validation;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\TestCase;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Validation\BouncedEmailInPostmark;

class BouncedEmailInPostmarkTest extends TestCase {
	
	/** @test */
	public function it_should_pass_for_valid_email() {
		
		$this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;
		
		$this->assertFileDoesNotExist($this->storagePath);
		
		$this->artisan('postmark-bounced-email:fetch')
			->assertExitCode(0);
		
		$validator = new BouncedEmailInPostmark;
		
		$this->assertTrue($validator->validate(NULL, env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_NOT_BLOCKED_EMAIL'), NULL, NULL));
	}
	
	/** @test */
	public function it_should_fail_for_blocked_email() {
		
		$this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;
		
		$this->assertFileDoesNotExist($this->storagePath);
		
		$this->artisan('postmark-bounced-email:fetch')
			->assertExitCode(0);
		
		$validator = new BouncedEmailInPostmark;
		
		$this->assertFalse($validator->validate(NULL, env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL'), NULL, NULL));
	}
	
	/** @test */
	public function it_is_usable_through_the_validator() {
		
		$this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;
		
		$this->assertFileDoesNotExist($this->storagePath);
		
		$this->artisan('postmark-bounced-email:fetch')
			->assertExitCode(0);
		
		$passingValidation = $this->app['validator']->make(['email' => env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_NOT_BLOCKED_EMAIL')], ['email' => 'bounced_email_in_postmark']);
		$failingValidation = $this->app['validator']->make(['email' => env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL')], ['email' => 'bounced_email_in_postmark']);
		
		$this->assertTrue($passingValidation->passes());
		$this->assertTrue($failingValidation->fails());
	}
}