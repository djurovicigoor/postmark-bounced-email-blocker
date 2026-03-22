<?php

use Djurovicigoor\PostmarkBouncedEmailBlocker\Validation\BouncedEmailInPostmark;

it('should pass for valid email', function () {
    $this->fakePostmarkApi(['thisaddressmarkedemailasspam@mywebsite.dev']);

    $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

    expect($this->storagePath)->not->toBeFile();

    $this->artisan('postmark-bounced-email:fetch')
        ->assertExitCode(0);

    $rule = new BouncedEmailInPostmark();
    $failed = false;

    $rule->validate('email', 'validemail@mywebsite.dev', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

it('should fail for blocked email', function () {
    $this->fakePostmarkApi(['thisaddressmarkedemailasspam@mywebsite.dev']);

    $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

    expect($this->storagePath)->not->toBeFile();

    $this->artisan('postmark-bounced-email:fetch')
        ->assertExitCode(0);

    $rule = new BouncedEmailInPostmark();
    $failed = false;

    $rule->validate('email', 'thisaddressmarkedemailasspam@mywebsite.dev', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeTrue();
});

it('is usable through the validator', function () {
    $this->fakePostmarkApi(['thisaddressmarkedemailasspam@mywebsite.dev']);

    $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;

    expect($this->storagePath)->not->toBeFile();

    $this->artisan('postmark-bounced-email:fetch')
        ->assertExitCode(0);

    $passingValidation = $this->app['validator']->make(
        ['email' => 'validemail@mywebsite.dev'],
        ['email' => new BouncedEmailInPostmark()]
    );
    $failingValidation = $this->app['validator']->make(
        ['email' => 'thisaddressmarkedemailasspam@mywebsite.dev'],
        ['email' => new BouncedEmailInPostmark()]
    );

    expect($passingValidation->passes())->toBeTrue()
        ->and($failingValidation->fails())->toBeTrue();
});
