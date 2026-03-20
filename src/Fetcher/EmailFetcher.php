<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Fetcher;

use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Djurovicigoor\PostmarkBouncedEmailBlocker\Contracts\Fetcher;

class EmailFetcher implements Fetcher
{
    /**
     * Fetch bounced/spam suppressed emails from the Postmark API.
     *
     *
     * @return array<int, string>
     *
     * @throws InvalidArgumentException|ConnectionException
     */
    public function handle(string $postmarkServerApiToken): array
    {
        if ($postmarkServerApiToken === '') {
            throw new InvalidArgumentException('Postmark token is not present.');
        }

        $response = Http::withHeaders([
            'Accept'                  => 'application/json',
            'X-Postmark-Server-Token' => $postmarkServerApiToken,
        ])->get('https://api.postmarkapp.com/message-streams/outbound/suppressions/dump');

        return collect($response->json('Suppressions'))->pluck('EmailAddress')->toArray();
    }
}
