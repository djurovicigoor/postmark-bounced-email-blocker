# Laravel Postmark Bounced Email Blocker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/djurovicigoor/postmark-bounced-email-blocker.svg?style=for-the-badge)](https://packagist.org/packages/djurovicigoor/postmark-bounced-email-blocker)
![Total Downloads](https://img.shields.io/packagist/dt/djurovicigoor/postmark-bounced-email-blocker.svg?style=for-the-badge)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

Adds a validator to Laravel for checking whether a given email address isn't blocked in your Postmark stream.

### Installation

1. Run the Composer require command to install the package:

    ```bash
    composer require djurovicigoor/postmark-bounced-email-blocker
    ```

2. If you don't use auto-discovery, open up your app config and add the Service Provider to the `$providers` array:

     ```php
    'providers' => [
        ...
     
        Djurovicigoor\PostmarkBouncedEmailBlocker\PostmarkBouncedEmailBlockerServiceProvider::class,
    ],
    ```

3. Publish the configuration file and adapt the configuration as desired:

   ```bash
   php artisan vendor:publish --tag=postmark-bounced-email-blocker
   ```

4. Run the following artisan command to fetch an up-to-date list of blocked emails:

    ```bash
    php artisan postmark-bounced-email:fetch
    ```

5. (optional) In your languages directory, add for each language an extra language line for the validator:

   ```php
   'bounced_email_in_postmark' => 'It\'s not possible to send email to this address because the recipient has flagged your previous email as spam.',
   ```

6. (optional) It's highly advised to update the blocked emails list regularly. You can either run the command yourself now and then or, if you make use of Laravel's scheduler, include it over there (`App\Console\Kernel`):

    ```php
    protected function schedule(Schedule $schedule)
	{
        $schedule->command('postmark-bounced-email:fetch')->daily();
	}
    ```

### Usage

Use the `bounced_email_in_postmark` validator to ensure a given field doesn't hold a blocked email address. You'll probably want to add it after the `email` validator to make sure a valid email is passed through:

```php
'field' => 'email|bounced_email_in_postmark',
```