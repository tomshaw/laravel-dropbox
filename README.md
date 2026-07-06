# Laravel Dropbox 📂 

A Laravel Dropbox API 2.0 client library.

![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/tomshaw/laravel-dropbox/run-tests.yml?branch=master&style=flat-square&label=tests)
![issues](https://img.shields.io/github/issues/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
![forks](https://img.shields.io/github/forks/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
![stars](https://img.shields.io/github/stars/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
[![GitHub license](https://img.shields.io/github/license/tomshaw/laravel-dropbox)](https://github.com/tomshaw/laravel-dropbox/blob/master/LICENSE)

## Requirements

- PHP 8.5
- Laravel 13.0

## Features

- **Secure OAuth 2.0 Flow**: Authorization with CSRF `state` verification and PKCE (S256) code challenges out of the box.
- **Automatic Token Refresh**: Expiring tokens are refreshed transparently before every authenticated request — in controllers, queued jobs, and Artisan commands alike.
- **Customizable Token Storage**: User definable token storage adapters to suit your apps needs. Database-stored tokens are encrypted at rest.
- **Static Token Mode**: Set `DROPBOX_ACCESS_TOKEN` to skip the OAuth flow entirely for single-account, server-side integrations.
- **Large File Support**: Uploads above Dropbox's 150 MB single-request limit automatically switch to chunked upload sessions; downloads can stream straight to disk.
- **Resilient HTTP Layer**: Built on Laravel's HTTP client with configurable timeouts and automatic retries that honor Dropbox `Retry-After` rate-limit headers.
- **Typed Exceptions**: `DropboxException`, `AuthenticationException`, and `RateLimitException` expose the status code and parsed `error_summary` from Dropbox.
- **Laravel Facades Integration**: Built using Laravel Facades offering a familiar and simple interface that promotes readability, flexibility, testing and ease of use.

## Installation

You can install the package via composer:

```bash
composer require tomshaw/laravel-dropbox
```

Next publish the configuration file:

```
php artisan vendor:publish --provider="TomShaw\Dropbox\Providers\DropboxServiceProvider" --tag=config
```

Run the migration if you wish to use database storage adapter:

```
php artisan migrate
```

## Configuration

Here's a breakdown of each configuration option:

> The following variables should be set in your `.env` file

- `DROPBOX_CLIENT_ID`: The client ID for your Dropbox application.

- `DROPBOX_CLIENT_SECRET`: The client secret for your Dropbox application.

- `DROPBOX_REDIRECT_URI`: The URI to redirect to after Dropbox authentication.

- `DROPBOX_ACCESS_TOKEN`: A static access token used for all API requests. When set, the OAuth flow and token storage are bypassed entirely.

- `DROPBOX_ACCESS_TYPE`: This is the access type for the Dropbox application.

- `DROPBOX_ACCESS_SCOPES`: If omitted will request all scopes selected on the Permissions tab.

- `DROPBOX_TIMEOUT`: Request timeout in seconds (default `30`).

- `DROPBOX_RETRIES`: Number of attempts for rate-limited requests or failed connections (default `3`).

Token storage is configured via the `storage` key in the published `config/dropbox.php`. The default is `DatabaseTokenStorage` (encrypted, requires the migration); `SessionTokenStorage` is included for session-scoped tokens, or provide your own implementation of `TomShaw\Dropbox\Storage\StorageAdapterInterface`.

> Developers should review the [Dropbox Developer Platform](https://www.dropbox.com/developers) and [SDK Documentation](https://www.dropbox.com/developers/documentation) for further information. 

## Basic Usage

> Below is a cursory explanation of this repository's usage. Please refer to the appropriate Facade Resource for additional methods and usage.

> Verify your apps credentials utilizing the `check` accessor `app` method.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function check()
    {
        Dropbox::check()->app();
    }
}
```

> Authorizing the application and persisting the token.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;

class DropboxController extends Controller
{
    public function connect()
    {
        if (request()->has('code')) {

            Dropbox::connect(request('code'), request('state'));

            return redirect()->route('dashboard');
        }

        return redirect()->away(Dropbox::getAuthUrl());
    }
}
```

> Revoking access using the `revoke` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;

class DropboxController extends Controller
{
    public function revoke()
    {
        Dropbox::revoke();

        return redirect()->route('dashboard');
    }
}
```

> Requesting account information using the `users` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;

class DropboxController extends Controller
{
    public function account()
    {
        Dropbox::users()->getCurrentAccount();
    }
}
```

> Creating folders using the `files` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;

class DropboxController extends Controller
{
    public function createfolder(string $path)
    {
        Dropbox::files()->createFolder($path, true);
    }
}
```

> Downloading files using the `files` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;

class DropboxController extends Controller
{
    public function download($id)
    {
        $item = Dropbox::files()->getMetadata($id);

        if ($item) {
            $fileContents = Dropbox::files()->download($item['path_lower']);

            return response($fileContents, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $item['name'] . '"',
            ]);
        }

        return abort(404);
    }
}
```

> Large downloads can stream directly to a local path without buffering in memory. The file metadata is returned from the `Dropbox-API-Result` header.

```php
$metadata = Dropbox::files()->downloadTo('/videos/demo.mp4', storage_path('app/demo.mp4'));
```

> Uploading files using the `files` accessor. Files above Dropbox's 150 MB single-request limit are automatically sent through a chunked upload session; use `uploadSession()` to force chunked uploads at any size.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\Enums\WriteMode;

class DropboxController extends Controller
{
    public function upload(string $destinationPath, string $sourceFilePath)
    {
        Dropbox::files()->upload($destinationPath, $sourceFilePath, mode: WriteMode::Add, autorename: false, mute: false, strictConflict: false);
    }
}
```

> Handling Dropbox API errors with typed exceptions.

```php
use TomShaw\Dropbox\Exceptions\{AuthenticationException, DropboxException, RateLimitException};

try {
    Dropbox::files()->getMetadata('/missing.txt');
} catch (RateLimitException $e) {
    // $e->retryAfter (seconds), after built-in retries were exhausted
} catch (AuthenticationException $e) {
    // Token invalid, revoked, or the OAuth flow has not completed
} catch (DropboxException $e) {
    // $e->status and $e->errorBody['error_summary']
}
```

> Sharing a link using the `sharing` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function sharelink(string $path)
    {
        Dropbox::sharing()->createSharedLinkWithSettings($path, ['requested_visibility' => 'public']);
    }
}
```

## Middleware

Add the included Dropbox `middleware` to any routes that require API access. Requests without a stored token are redirected to the Dropbox authorization page; requests expecting JSON receive a `401` response instead.

```php
Route::group(['middleware' => ['web', 'auth', 'dropbox']], function () {
  /* Grouped routes */
});
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Changelog

For changes made to the project, see the [Changelog](CHANGELOG.md).

## License

The MIT License (MIT). See [License File](LICENSE) for more information.

