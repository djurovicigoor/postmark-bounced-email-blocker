<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Tests;

use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;

class PostmarkBouncedEmailBlockerTest extends TestCase {
	
	/** @test */
	public function it_can_be_resolved_using_alias() {
		
		$this->assertEquals(PostmarkBouncedEmailBlocker::class, get_class($this->app->make('postmark_bounced.emails')));
	}
	
	/** @test */
	public function it_can_be_resolved_using_class() {
		
		$this->assertEquals(PostmarkBouncedEmailBlocker::class, get_class($this->app->make(PostmarkBouncedEmailBlocker::class)));
	}
	
	/** @test */
	public function it_can_get_storage_path() {
		
		$this->assertEquals(
			$this->app['config']['postmark-bounced-email-blocker.storage'],
			$this->postmarkBouncedEmails()->getStoragePath()
		);
	}
	
	/** @test */
	public function it_can_set_storage_path() {
		
		$this->postmarkBouncedEmails()->setStoragePath('foo-bar');
		
		$this->assertEquals('foo-bar', $this->postmarkBouncedEmails()->getStoragePath());
	}
	
	/** @test */
	public function it_can_get_cache_key() {
		
		$this->assertEquals(
			$this->app['config']['postmark-bounced-email-blocker.cache.key'],
			$this->postmarkBouncedEmails()->getCacheKey()
		);
	}
	
	/** @test */
	public function it_can_set_cache_key() {
		
		$this->postmarkBouncedEmails()->setCacheKey('foo-bar');
		
		$this->assertEquals('foo-bar', $this->postmarkBouncedEmails()->getCacheKey());
	}
	
	/** @test */
	public function it_takes_cached_emails_if_available() {
		
		$this->app['cache.store'][ $this->postmarkBouncedEmails()->getCacheKey() ] = ['foo-bar'];
		
		$this->postmarkBouncedEmails()->bootstrap();
		
		$emails = $this->postmarkBouncedEmails()->getEmails();
		
		$this->assertEquals(['foo-bar'], $emails);
	}
	
	/** @test */
	public function it_flushes_invalid_cache_values() {
		
		$this->app['cache.store'][ $this->postmarkBouncedEmails()->getCacheKey() ] = 'foo-bar';
		
		$this->postmarkBouncedEmails()->bootstrap();
		
		$this->assertNotEquals('foo-bar', $this->app['cache.store'][ $this->postmarkBouncedEmails()->getCacheKey() ]);
	}
	
	/** @test */
	public function it_skips_cache_when_configured() {
		
		$this->app['config']['postmark-bounced-email-blocker.cache.enabled'] = FALSE;
		
		$emails = $this->postmarkBouncedEmails()->getEmails();
		
		$this->assertIsArray($emails);
		$this->assertNull($this->app['cache.store'][ $this->postmarkBouncedEmails()->getCacheKey() ]);
		$this->assertContains('thisaddressmarkedemailasspam@mywebsite.dev', $emails);
	}
	
	/** @test */
	public function it_takes_storage_emails_when_cache_is_not_available() {
		
		$this->app['config']['postmark-bounced-email-blocker.cache.enabled'] = FALSE;
		
		file_put_contents($this->storagePath, json_encode(['thisaddressmarkedemailasspam@mywebsite.dev']));
		
		$this->postmarkBouncedEmails()->bootstrap();
		
		$emails = $this->postmarkBouncedEmails()->getEmails();
		
		$this->assertEquals(['thisaddressmarkedemailasspam@mywebsite.dev'], $emails);
	}
	
	/** @test */
	public function it_can_flush_storage() {
		
		$this->postmarkBouncedEmails()->setStoragePath($this->storagePath);
		
		file_put_contents($this->storagePath, json_encode(['thisaddressmarkedemailasspam@mywebsite.dev']));
		
		$this->postmarkBouncedEmails()->flushStorage();
		
		$this->assertFileDoesNotExist($this->storagePath);
	}
	
	/** @test */
	public function it_doesnt_throw_exceptions_for_flush_storage_when_file_doesnt_exist() {
		
		$this->postmarkBouncedEmails()->flushStorage();
		
		$this->assertTrue(TRUE);
	}
	
	/** @test */
	public function it_can_flush_cache() {
		
		$this->app['cache.store'][ $this->postmarkBouncedEmails()->getCacheKey() ] = 'foo-bar';
		
		$this->assertEquals('foo-bar', $this->app['cache']->get($this->postmarkBouncedEmails()->getCacheKey()));
		
		$this->postmarkBouncedEmails()->flushCache();
		
		$this->assertNull($this->app['cache']->get($this->postmarkBouncedEmails()->getCacheKey()));
	}
	
	/** @test */
	public function it_can_verify_is_blocked() {
		
		$this->assertTrue($this->postmarkBouncedEmails()->isBlocked('thisaddressmarkedemailasspam@mywebsite.dev'));
		$this->assertFalse($this->postmarkBouncedEmails()->isNotBlocked('thisaddressmarkedemailasspam@mywebsite.dev'));
		
		$this->assertFalse($this->postmarkBouncedEmails()->isBlocked('validemail@mywebsite.dev'));
		$this->assertTrue($this->postmarkBouncedEmails()->isNotBlocked('validemail@mywebsite.dev'));
	}
	
}