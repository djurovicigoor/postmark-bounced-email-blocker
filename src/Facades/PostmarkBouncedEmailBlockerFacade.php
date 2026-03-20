<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Facades;

use Illuminate\Support\Facades\Facade;
use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;

/**
 * @method static bool isBlocked(?string $email)
 * @method static bool isNotBlocked(?string $email)
 * @method static array<int, string> getEmails()
 * @method static void flushCache()
 * @method static void flushStorage()
 * @method static void saveToCache(?array $emails = null)
 * @method static bool|int saveToStorage(array $emails)
 * @method static static bootstrap()
 * @method static string getStoragePath()
 * @method static static setStoragePath(string $path)
 * @method static string getCacheKey()
 * @method static static setCacheKey(string $key)
 *
 * @see PostmarkBouncedEmailBlocker
 */
class PostmarkBouncedEmailBlockerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PostmarkBouncedEmailBlocker::class;
    }
}
