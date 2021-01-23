# laravel-getid3

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![StyleCI](https://github.styleci.io/repos/163754555/shield?branch=master)](https://github.styleci.io/repos/163754555)

This package is a wrapper around *james-heinrich/getid3*.<br>

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
``` php
use Owenoj\LaravelGetId3\GetId3;

//instantiate class with file
$track = new GetId3(request()->file('file'));

// Use static methods:
$track = GetId3::fromUploadedFile(request()->file('file'));
$track = GetId3::fromDiskAndPath('local', '/some/file.mp3');
$track = GetId3::fromDiskAndPath('s3', '/some/file.mp3'); // even works with S3

//get all info
$track->extractInfo();

//get title
$track->getTitle();

//get playtime
$track->getPlaytime();
```

We can also extract the artwork from the file
```php
//calling this method will return artwork in base64 string
$track->getArtwork();
//Optionally you can pass can pass `true` to the method to get a jpeg version. This will return an UploadedFile instance
$track->getArtwork(true);
```

## Available Methods

#### extractInfo() : array 
Get an array of all available metadata of file
#### getArtist() : string      
 Get the artist of the track
#### getTitle() : string      
Get the title of the track
#### getAlbum() : string       
Get name of Album
#### getPlaytime() : string    
Get a tracks total playtime  
#### getPlaytimeSeconds() : float
Get total playtime in seconds
#### getArtwork() 
Get the artwork of the track
#### getGenres() : array
Get the list of genres
#### getArtist() : string
Get the artist of the track
#### getComposer() : string
Get the composers of the track
#### getTrackNumber() : string
Get the track number out of total number on album eg. 1/12
#### getCopyrightInfo() : string
Get copyright information of the track
#### getFileFormat() : string
Get the file format of the file eg. mp4

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
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-downloads]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/owen-oj
[link-contributors]: ../../contributors]
