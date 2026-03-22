<?php

use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;

it('can be resolved using alias', function () {
    expect(get_class($this->app->make('postmark_bounced.emails')))
        ->toBe(PostmarkBouncedEmailBlocker::class);
});

it('can be resolved using class', function () {
    expect(get_class($this->app->make(PostmarkBouncedEmailBlocker::class)))
        ->toBe(PostmarkBouncedEmailBlocker::class);
});

it('can get storage path', function () {
    expect($this->postmarkBouncedEmails()->getStoragePath())
        ->toBe($this->app['config']['postmark-bounced-email-blocker.storage']);
});

it('can set storage path', function () {
    $this->postmarkBouncedEmails()->setStoragePath('foo-bar');

    expect($this->postmarkBouncedEmails()->getStoragePath())
        ->toBe('foo-bar');
});

it('can get cache key', function () {
    expect($this->postmarkBouncedEmails()->getCacheKey())
        ->toBe($this->app['config']['postmark-bounced-email-blocker.cache.key']);
});

it('can set cache key', function () {
    $this->postmarkBouncedEmails()->setCacheKey('foo-bar');

    expect($this->postmarkBouncedEmails()->getCacheKey())
        ->toBe('foo-bar');
});

it('takes cached emails if available', function () {
    $this->app['cache.store'][$this->postmarkBouncedEmails()->getCacheKey()] = ['foo-bar'];

    $this->postmarkBouncedEmails()->bootstrap();

    expect($this->postmarkBouncedEmails()->getEmails())
        ->toBe(['foo-bar']);
});

it('flushes invalid cache values', function () {
    $this->app['cache.store'][$this->postmarkBouncedEmails()->getCacheKey()] = 'foo-bar';

    $this->postmarkBouncedEmails()->bootstrap();

    expect($this->app['cache.store'][$this->postmarkBouncedEmails()->getCacheKey()])
        ->not->toBe('foo-bar');
});

it('skips cache when configured', function () {
    $this->app['config']['postmark-bounced-email-blocker.cache.enabled'] = false;

    $emails = $this->postmarkBouncedEmails()->getEmails();

    expect($emails)->toBeArray()
        ->and($this->app['cache.store'][$this->postmarkBouncedEmails()->getCacheKey()])->toBeNull()
        ->and($emails)->toContain('thisaddressmarkedemailasspam@mywebsite.dev');
});

it('takes storage emails when cache is not available', function () {
    $this->app['config']['postmark-bounced-email-blocker.cache.enabled'] = false;

    file_put_contents($this->storagePath, json_encode(['thisaddressmarkedemailasspam@mywebsite.dev']));

    $this->postmarkBouncedEmails()->bootstrap();

    expect($this->postmarkBouncedEmails()->getEmails())
        ->toBe(['thisaddressmarkedemailasspam@mywebsite.dev']);
});

it('can flush storage', function () {
    $this->postmarkBouncedEmails()->setStoragePath($this->storagePath);

    file_put_contents($this->storagePath, json_encode(['thisaddressmarkedemailasspam@mywebsite.dev']));

    $this->postmarkBouncedEmails()->flushStorage();

    expect($this->storagePath)->not->toBeFile();
});

it('does not throw exceptions for flush storage when file does not exist', function () {
    $this->postmarkBouncedEmails()->flushStorage();

    expect(true)->toBeTrue();
});

it('can flush cache', function () {
    $this->app['cache.store'][$this->postmarkBouncedEmails()->getCacheKey()] = 'foo-bar';

    expect($this->app['cache']->get($this->postmarkBouncedEmails()->getCacheKey()))
        ->toBe('foo-bar');

    $this->postmarkBouncedEmails()->flushCache();

    expect($this->app['cache']->get($this->postmarkBouncedEmails()->getCacheKey()))
        ->toBeNull();
});

it('can verify is blocked', function () {
    expect($this->postmarkBouncedEmails()->isBlocked('thisaddressmarkedemailasspam@mywebsite.dev'))->toBeTrue()
        ->and($this->postmarkBouncedEmails()->isNotBlocked('thisaddressmarkedemailasspam@mywebsite.dev'))->toBeFalse()
        ->and($this->postmarkBouncedEmails()->isBlocked('validemail@mywebsite.dev'))->toBeFalse()
        ->and($this->postmarkBouncedEmails()->isNotBlocked('validemail@mywebsite.dev'))->toBeTrue();
});
