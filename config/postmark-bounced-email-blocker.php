<?php
/**
 * config/postmark-bounced-email-blocker.php
 * Postmark bounced email blocker configuration file.
 * Created by PhpStorm.
 * Date: 15.06.23.
 * Time: 19.45
 *
 * @package Postmark bounced email blocker
 * @author  Djurovic Igor djurovic.igoor@gmail.com
 */
return [
	/*
	|--------------------------------------------------------------------------
	| Server API token from your Postmark account.
	|--------------------------------------------------------------------------
	*/
	'server-api-token' => env('POSTMARK_BOUNCED_EMAIL_BLOCKER_SERVER_API_TOKEN', null),
	
	
	/*
	|--------------------------------------------------------------------------
	| Storage Path
	|--------------------------------------------------------------------------
	|
	| The location where the retrieved emails list should be stored locally.
	| The path should be accessible and writable by the web server. A good
	| place for storing the list is in the framework's own storage path.
	|
	*/
	
	'storage' => storage_path('framework/postmark-bounced-emails.json'),
	
	/*
	|--------------------------------------------------------------------------
	| Cache Configuration
	|--------------------------------------------------------------------------
	|
	| Here you may define whether the emails list should be cached.
	| If you disable caching or when the cache is empty, the list will be
	| fetched from local storage instead.
	|
	| You can optionally specify an alternate cache connection or modify the
	| cache key as desired.
	|
	*/
	
	'cache' => [
		'enabled' => TRUE,
		'store'   => 'default',
		'key'     => 'postmark_bounced:emails',
	],
];