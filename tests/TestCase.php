<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;

abstract class TestCase extends BaseTestCase
{
    protected string $storagePath = __DIR__.'/postmark-bounced-emails.json';

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('postmark-bounced-email-blocker.server-api-token', 'test-api-token');

        parent::getEnvironmentSetUp($app);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->postmarkBouncedEmails()->flushStorage();
        $this->postmarkBouncedEmails()->flushCache();
    }

    protected function tearDown(): void
    {
        $this->postmarkBouncedEmails()->flushStorage();
        $this->postmarkBouncedEmails()->flushCache();

        parent::tearDown();
    }

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return ['Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlockerServiceProvider'];
    }

    /**
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return ['PostmarkBouncedEmailBlockerFacade' => 'Djurovicigoor\PostmarkBouncedEmailBlocker\Facades\PostmarkBouncedEmailBlockerFacade'];
    }

    protected function postmarkBouncedEmails(): PostmarkBouncedEmailBlocker
    {
        return $this->app['postmark_bounced.emails'];
    }

    /**
     * Fake the Postmark API response for testing.
     *
     * @param  array<int, string>  $emails
     */
    protected function fakePostmarkApi(array $emails = ['thisaddressmarkedemailasspam@mywebsite.dev']): void
    {
        Http::fake([
            'api.postmarkapp.com/*' => Http::response([
                'Suppressions' => collect($emails)->map(fn (string $email): array => [
                    'EmailAddress' => $email,
                    'SuppressionReason' => 'ManualSuppression',
                    'CreatedAt' => '2023-06-15T00:00:00.0000000+00:00',
                ])->all(),
            ]),
        ]);
    }
}
