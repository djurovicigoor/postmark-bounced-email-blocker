<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker;

use Psr\SimpleCache\InvalidArgumentException;
use Illuminate\Contracts\Cache\Repository as Cache;

class PostmarkBouncedEmailBlocker
{
    
    /**
     * The storage path to retrieve from and save to.
     *
     * @var string
     */
    protected string $storagePath;
    
    /**
     * Array of retrieved blocked emails.
     *
     * @var array
     */
    protected array $emails = [];
    
    /**
     * The cache repository.
     *
     * @var Cache|null
     */
    protected ?Cache $cache;
    
    /**
     * The cache key.
     *
     * @var string
     */
    protected string $cacheKey;
    
    /**
     * Postmark bounced email blocker constructor.
     *
     * @param  Cache|null  $cache
     */
    public function __construct(Cache $cache = null)
    {
        $this->cache = $cache;
    }
    
    /**
     * Loads the emails from cache/storage into the class.
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function bootstrap(): static
    {
        $emails = $this->getFromCache();
        
        if ( ! $emails) {
            $this->saveToCache($emails = $this->getFromStorage());
        }
        
        $this->emails = $emails;
        
        return $this;
    }
    
    /**
     * Get the emails from cache.
     *
     * @return array|null|string
     * @throws InvalidArgumentException
     */
    protected function getFromCache(): mixed
    {
        if ($this->cache) {
            $emails = $this->cache->get($this->getCacheKey());
            
            if (is_string($emails) || empty($emails)) {
                $this->flushCache();
                
                return null;
            }
            
            return $emails;
        }
        
        return null;
    }
    
    /**
     * Save the emails in cache.
     *
     * @param  array|null  $emails
     */
    public function saveToCache(array $emails = null): void
    {
        if ($this->cache && ! empty($emails)) {
            $this->cache->forever($this->getCacheKey(), $emails);
        }
    }
    
    /**
     * Checks whether the given email address matches a blocked email in Postmark.
     *
     * @param  string|null  $email
     *
     * @return bool
     */
    public function isBlocked(string $email = null): bool
    {
        if ($email) {
            return in_array($email, $this->emails);
        }
        
        // Just ignore this validator if the value doesn't even resemble an email.
        return false;
    }
    
    /**
     * Checks whether the given email address doesn't match a blocked email in Postmark.
     *
     * @param  string|null  $email
     *
     * @return bool
     */
    public function isNotBlocked(string $email = null): bool
    {
        
        return ! $this->isBlocked($email);
    }
    
    /**
     * Get the emails from storage, or if non-existent, from the package.
     *
     * @return array
     */
    protected function getFromStorage(): array
    {
        
        $emails = is_file($this->getStoragePath()) ? file_get_contents($this->getStoragePath()) : file_get_contents(__DIR__.'/../postmark-bounced-emails.json');
        
        return json_decode($emails, true);
    }
    
    /**
     * Save the emails in storage.
     *
     * @param  array  $emails
     *
     * @return bool|int
     */
    public function saveToStorage(array $emails): bool|int
    {
        
        $saved = file_put_contents($this->getStoragePath(), json_encode($emails));
        
        if ($saved) {
            $this->flushCache();
        }
        
        return $saved;
    }
    
    /**
     * Flushes the cache if applicable.
     */
    public function flushCache(): void
    {
        
        $this->cache?->forget($this->getCacheKey());
    }
    
    /**
     * Flushes the source's list if applicable.
     */
    public function flushStorage(): void
    {
        
        if (is_file($this->getStoragePath())) {
            @unlink($this->getStoragePath());
        }
    }
    
    /**
     * Get the storage path.
     *
     * @return string
     */
    public function getStoragePath(): string
    {
        
        return $this->storagePath;
    }
    
    /**
     * Set the storage path.
     *
     * @param  string  $path
     *
     * @return $this
     */
    public function setStoragePath(string $path): static
    {
        
        $this->storagePath = $path;
        
        return $this;
    }
    
    /**
     * Get the cache key.
     *
     * @return string
     */
    public function getCacheKey(): string
    {
        
        return $this->cacheKey;
    }
    
    /**
     * Set the cache key.
     *
     * @param  string  $key
     *
     * @return $this
     */
    public function setCacheKey(string $key): static
    {
        
        $this->cacheKey = $key;
        
        return $this;
    }
    
    /**
     * Get the list of blocked emails.
     *
     * @return array
     */
    public function getEmails(): array
    {
        
        return $this->emails;
    }
}