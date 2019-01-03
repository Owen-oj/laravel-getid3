# laravel-getid3

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI](https://github.styleci.io/repos/163754555/shield?branch=master)](https://github.styleci.io/repos/163754555)

This package is a wrapper around *james-heinrich/getid3*.<br>
**NB: This package is still in development**

## Installation

Via Composer

``` bash
$ composer require owen-oj/laravel-getid3
```

If you use Laravel 5.5+ you don't need the following step. If not, once package is installed, you need to register the service provider. Open config/app.php and add the following to the providers key.
``` bash
 Owenoj\LaravelGetId3\GetId3ServiceProvider::class,
```

## Usage
using facade
``` php
   $trackInfo = MediaInfo::extract($path_to_file);
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email owen.j@terktrendz.com instead of using the issue tracker.

## Credits

- [Owen Jubilant][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/owen-oj/laravel-getid3.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/owen-oj/laravel-getid3.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/owen-oj/laravel-getid3/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-downloads]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-travis]: https://travis-ci.org/owen-oj/laravel-getid3
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/owen-oj
[link-contributors]: ../../contributors]
