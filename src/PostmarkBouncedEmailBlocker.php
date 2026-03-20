<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker;

use Psr\SimpleCache\InvalidArgumentException;
use Illuminate\Contracts\Cache\Repository as Cache;

class PostmarkBouncedEmailBlocker
{
    /**
     * The storage path to retrieve from and save to.
     */
    protected string $storagePath;

    /**
     * Array of retrieved blocked emails.
     *
     * @var array<int, string>
     */
    protected array $emails = [];

    /**
     * The cache repository.
     */
    protected ?Cache $cache;

    /**
     * The cache key.
     */
    protected string $cacheKey;

    /**
     * Create a new PostmarkBouncedEmailBlocker instance.
     */
    public function __construct(?Cache $cache = null)
    {
        $this->cache = $cache;
    }

    /**
     * Load the emails from cache/storage into the class.
     *
     * @throws InvalidArgumentException
     */
    public function bootstrap(): static
    {
        $emails = $this->getFromCache();

        if (! $emails) {
            $this->saveToCache($emails = $this->getFromStorage());
        }

        $this->emails = $emails;

        return $this;
    }

    /**
     * Get the emails from cache.
     *
     * @return array<int, string>|null
     *
     * @throws InvalidArgumentException
     */
    protected function getFromCache(): ?array
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
     * @param  array<int, string>|null  $emails
     */
    public function saveToCache(?array $emails = null): void
    {
        if ($this->cache && ! empty($emails)) {
            $this->cache->forever($this->getCacheKey(), $emails);
        }
    }

    /**
     * Check whether the given email address matches a blocked email in Postmark.
     */
    public function isBlocked(?string $email = null): bool
    {
        if ($email) {
            return in_array($email, $this->emails);
        }

        return false;
    }

    /**
     * Check whether the given email address does not match a blocked email in Postmark.
     */
    public function isNotBlocked(?string $email = null): bool
    {
        return ! $this->isBlocked($email);
    }

    /**
     * Get the emails from storage, or if non-existent, from the package default.
     *
     * @return array<int, string>
     */
    protected function getFromStorage(): array
    {
        $emails = is_file($this->getStoragePath())
            ? file_get_contents($this->getStoragePath())
            : file_get_contents(__DIR__.'/../postmark-bounced-emails.json');

        return json_decode($emails, true);
    }

    /**
     * Save the emails to storage.
     *
     * @param  array<int, string>  $emails
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
     * Flush the cache if applicable.
     */
    public function flushCache(): void
    {
        $this->cache?->forget($this->getCacheKey());
    }

    /**
     * Flush the storage file if applicable.
     */
    public function flushStorage(): void
    {
        if (is_file($this->getStoragePath())) {
            @unlink($this->getStoragePath());
        }
    }

    /**
     * Get the storage path.
     */
    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    /**
     * Set the storage path.
     */
    public function setStoragePath(string $path): static
    {
        $this->storagePath = $path;

        return $this;
    }

    /**
     * Get the cache key.
     */
    public function getCacheKey(): string
    {
        return $this->cacheKey;
    }

    /**
     * Set the cache key.
     */
    public function setCacheKey(string $key): static
    {
        $this->cacheKey = $key;

        return $this;
    }

    /**
     * Get the list of blocked emails.
     *
     * @return array<int, string>
     */
    public function getEmails(): array
    {
        return $this->emails;
    }
}
