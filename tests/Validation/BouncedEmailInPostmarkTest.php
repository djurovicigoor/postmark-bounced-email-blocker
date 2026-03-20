<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\Validation;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Tests\TestCase;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Validation\BouncedEmailInPostmark;

class BouncedEmailInPostmarkTest extends TestCase
{
    /** @test */
    public function it_should_pass_for_valid_email()
    {

        $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('postmark-bounced-email:fetch')
            ->assertExitCode(0);

        $rule = new BouncedEmailInPostmark();
        $failed = false;

        $rule->validate('email', env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_NOT_BLOCKED_EMAIL'), function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    /** @test */
    public function it_should_fail_for_blocked_email()
    {

        $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('postmark-bounced-email:fetch')
            ->assertExitCode(0);

        $rule = new BouncedEmailInPostmark();
        $failed = false;

        $rule->validate('email', env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL'), function () use (&$failed) {
            $failed = true;
        });

        $this->assertTrue($failed);
    }

    /** @test */
    public function it_is_usable_through_the_validator()
    {

        $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

        $this->assertFileDoesNotExist($this->storagePath);

        $this->artisan('postmark-bounced-email:fetch')
            ->assertExitCode(0);

        $passingValidation = $this->app['validator']->make(
            ['email' => env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_NOT_BLOCKED_EMAIL')],
            ['email' => new BouncedEmailInPostmark()]
        );
        $failingValidation = $this->app['validator']->make(
            ['email' => env('POSTMARK_BOUNCED_EMAIL_BLOCKER_TESTING_BLOCKED_EMAIL')],
            ['email' => new BouncedEmailInPostmark()]
        );

        $this->assertTrue($passingValidation->passes());
        $this->assertTrue($failingValidation->fails());
    }
}
