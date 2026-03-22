<?php

it('creates the file', function () {
    $this->fakePostmarkApi(['thisaddressmarkedemailasspam@mywebsite.dev']);

    $this->app['config']['postmark-bounced-email-blocker.storage'] = $this->storagePath;
    $this->postmarkBouncedEmails()->setStoragePath($this->storagePath);

    expect($this->storagePath)->not->toBeFile();

    $this->artisan('postmark-bounced-email:fetch')
        ->assertExitCode(0);

    expect($this->storagePath)->toBeFile();

    $emails = $this->postmarkBouncedEmails()->getEmails();

    expect($emails)->toBeArray()
        ->and($emails)->toContain('thisaddressmarkedemailasspam@mywebsite.dev');
});

it('overwrites the file', function () {
    $this->fakePostmarkApi(['thisaddressmarkedemailasspam@mywebsite.dev']);

    file_put_contents($this->storagePath, json_encode(['foo-bar']));

    $this->artisan('postmark-bounced-email:fetch')
        ->assertExitCode(0);

    expect($this->storagePath)->toBeFile();

    $emails = $this->postmarkBouncedEmails()->getEmails();

    expect($emails)->toBeArray()
        ->and($emails)->toContain('thisaddressmarkedemailasspam@mywebsite.dev')
        ->and($emails)->not->toContain('foo-bar');
});
