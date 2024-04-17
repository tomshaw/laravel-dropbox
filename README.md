# Dropbox 📂 

A Laravel Dropbox API client library.

![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/tomshaw/laravel-dropbox/run-tests.yml?branch=master&style=flat-square&label=tests)
![issues](https://img.shields.io/github/issues/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
![forks](https://img.shields.io/github/forks/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
![stars](https://img.shields.io/github/stars/tomshaw/laravel-dropbox?style=flat&logo=appveyor)
[![GitHub license](https://img.shields.io/github/license/tomshaw/laravel-dropbox)](https://github.com/tomshaw/laravel-dropbox/blob/master/LICENSE)

## Features

- **Customizable Token Storage**: User definable token storage adapters to suit your apps needs providing flexibility in how and where you store your tokens.
- **Token Refresh Middleware**: Automatically handles token refreshing, ensuring your application maintains a valid API connection without manual intervention.
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

- `DROPBOX_ACCESS_TOKEN`: This is the access token for Dropbox API requests.

- `DROPBOX_ACCESS_TYPE`: This is the access type for the Dropbox application.

- `DROPBOX_ACCESS_SCOPES`: If omitted will request all scopes selected on the Permissions tab.

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
        Dropbox::check()->app(['query' => 'foo']);
    }
}
```

> Authorizing the application and persisting the token.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function connect()
    {
        if (request()->has('code')) {

            Dropbox::connect(request('code'));

            return redirect()->route('dashboard');
        }

        return redirect()->away(Dropbox::getAuthUrl());
    }
}
```

> Revoking access tokens utilizing the `auth` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function revoke()
    {
        Dropbox::revoke();

        return redirect()->route('dashboard');
    }
}
```

> Request account information utilizing the `users` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function account()
    {
        Dropbox::users()->getCurrentAccount();
    }
}
```

> Creating a folder utilizing the `files` accessor.

```php
namespace App\Http\Controllers;

use TomShaw\Dropbox\Dropbox;
use TomShaw\Dropbox\DropboxClient;

class DropboxController extends Controller
{
    public function createfolder(string $path)
    {
        Dropbox::files()->createFolder($path, true);
    }
}
```

> Sharing a link utilizing the `sharing` accessor.

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

Add the included Dropbox `middleware` to any routes that require API access.

```php
Route::group(['middleware' => ['web', 'auth', 'dropbox']], function () {
  /* Grouped routes */
});
```

## Requirements

The package is compatible with PHP 8 or later.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). See [License File](LICENSE) for more information.

