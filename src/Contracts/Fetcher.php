<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Contracts;

interface Fetcher {
	
	public function handle($postmarkServerApiToken): array;
}