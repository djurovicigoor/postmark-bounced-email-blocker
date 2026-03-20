<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Contracts;

interface Fetcher
{
    /**
     * Fetch bounced/spam emails from the source.
     *
     * @return array<int, string>
     */
    public function handle(string $postmarkServerApiToken): array;
}
