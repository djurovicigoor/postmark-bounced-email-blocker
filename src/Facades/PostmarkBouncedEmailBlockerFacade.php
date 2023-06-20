<?php

namespace Djurovicigoor\PostmarkBouncedEmailBlocker\Facades;

use Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlocker;
use Illuminate\Support\Facades\Facade;

/*
 * @method static bool isBlocked(string $email)
 * @see PostmarkBouncedEmailBlocker
 * 
 * */
class PostmarkBouncedEmailBlockerFacade extends Facade {
	
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor(): string {
		
		return PostmarkBouncedEmailBlocker::class;
	}
}