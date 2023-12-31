<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Fetcher;

use Djurovicigoor\PostmarkBouncedEmailBlocker\Contracts\Fetcher;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class EmailFetcher implements Fetcher {
	
	public function handle($postmarkServerApiToken): array {
		
		if ($postmarkServerApiToken === NULL || $postmarkServerApiToken === '') {
			throw new InvalidArgumentException('Postmark token is not present.');
		}
		
		$response = Http::withHeaders([
			'Accept'                  => 'application/json',
			'X-Postmark-Server-Token' => $postmarkServerApiToken,
		])->get('https://api.postmarkapp.com/message-streams/outbound/suppressions/dump', [
			//			'SuppressionReason'  => 'SpamComplaint',
		]);
		
		return collect($response->json('Suppressions'))->pluck('EmailAddress')->toArray();
	}
}