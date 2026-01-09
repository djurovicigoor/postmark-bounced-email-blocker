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
     * Array of derived blocked domains (lowercased), built from $emails.
     *
     * @var array
     */
    protected array $domains = [];

    /**
     * Whether to consider an email blocked if its domain matches any domain from blocked emails.
     * Disabled by default to preserve existing behavior.
     */
    protected bool $blockDomain = false;
    
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
        // Derive domains from the loaded emails so we can optionally block entire domains.
        $this->domains = $this->extractDomainsFromEmails($this->emails);
        
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
     * Checks whether the given email address matches a blocked email (or domain, if enabled).
     *
     * @param  string  $email
     *
     * @return bool
     */
    public function isBlocked(string $email): bool
    {
        if ($email) {
            // Exact match first (preserve original behavior)
            if (in_array($email, $this->emails, true)) {
                return true;
            }
            
            // If domain-based blocking is enabled, block when the domain of the email is in the derived list.
            if ($this->blockDomain) {
                $domain = strtolower(ltrim(strrchr($email, '@') ?: '', '@'));
                if ($domain !== '' && in_array($domain, $this->domains, true)) {
                    return true;
                }
            }
            
            return false;
        }
        
        // Just ignore this validator if the value doesn't even resemble an email.
        return false;
    }
    
    /**
     * Checks whether the given email address doesn't match a blocked email in Postmark.
     *
     * @param  string  $email
     *
     * @return bool
     */
    public function isNotBlocked(string $email): bool
    {
        return ! $this->isBlocked($email);
    }

    /**
     * Enable or disable domain-based blocking.
     *
     * When enabled, if any address from a domain is blocked (e.g., noreply2@PatwerkGlobal.onmicrosoft.com),
     * then all addresses from that domain (e.g., anything@PatwerkGlobal.onmicrosoft.com) will be considered blocked.
     */
    public function setBlockDomain(bool $block): static
    {
        $this->blockDomain = $block;
        return $this;
    }

    /**
     * Convenience method to enable domain-based blocking.
     */
    public function enableDomainBlocking(): static
    {
        return $this->setBlockDomain(true);
    }

    /**
     * Convenience method to disable domain-based blocking.
     */
    public function disableDomainBlocking(): static
    {
        return $this->setBlockDomain(false);
    }

    /**
     * Extract unique, lowercased domains from a list of email addresses.
     *
     * @param  array  $emails
     * @return array
     */
    protected function extractDomainsFromEmails(array $emails): array
    {
        $domains = [];
        foreach ($emails as $blockedEmail) {
            if (! is_string($blockedEmail)) {
                continue;
            }
            $domain = strtolower(ltrim(strrchr($blockedEmail, '@') ?: '', '@'));
            if ($domain !== '') {
                $domains[$domain] = true; // use assoc for uniqueness
            }
        }
        return array_keys($domains);
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
