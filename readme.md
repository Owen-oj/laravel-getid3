# laravel-getid3

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![StyleCI](https://github.styleci.io/repos/163754555/shield?branch=master)](https://github.styleci.io/repos/163754555)

A Laravel wrapper around [james-heinrich/getid3](https://github.com/JamesHeinrich/getID3) that extracts audio and video metadata from local files, uploaded files, and remote storage disks (including S3).

[!["Buy Me A Coffee"](https://www.buymeacoffee.com/assets/img/custom_images/orange_img.png)](https://www.buymeacoffee.com/kkoj)

## Installation

```bash
composer require owen-oj/laravel-getid3
```

> Laravel 5.5+ auto-discovers the service provider. For older versions add `Owenoj\LaravelGetId3\GetId3ServiceProvider::class` to the `providers` array in `config/app.php`.

## Instantiation

```php
use Owenoj\LaravelGetId3\GetId3;

// From an uploaded file (e.g. from a form request)
$track = GetId3::fromUploadedFile(request()->file('file'));

// From a Laravel storage disk (works with local, S3, etc.)
$track = GetId3::fromDiskAndPath('local', '/some/file.mp3');
$track = GetId3::fromDiskAndPath('s3', 'uploads/video.mp4');

// Direct constructor with a file path
$track = new GetId3('/absolute/path/to/file.flac');
```

## Quick start

```php
// Raw array of everything getID3 can extract
$track->extractInfo();

// Common audio tags
$track->getTitle();          // "Bohemian Rhapsody"
$track->getArtist();         // "Queen"
$track->getAlbum();          // "A Night at the Opera"
$track->getYear();           // "1975"
$track->getPlaytime();       // "5:55"
$track->getPlaytimeSeconds(); // 354.63

// Common video info
$track->getVideoWidth();     // 1920
$track->getVideoHeight();    // 1080
$track->getVideoAspectRatio(); // "16:9"
$track->getFrameRate();      // 29.97
$track->getVideoCodec();     // "h264"

// Check what kind of media it is
$track->isAudio();  // true / false
$track->isVideo();  // true / false
```

## Available Methods

### File information

| Method                   | Return type    | Description                                                |
|--------------------------|----------------|------------------------------------------------------------|
| `extractInfo()`          | `array`        | Raw getID3 data array — everything the library can extract |
| `getFileFormat()`        | `string\|null` | Container/wrapper format (e.g. `"mp3"`, `"mp4"`, `"flac"`) |
| `getFileSize()`          | `int\|null`    | File size in bytes                                         |
| `getFileSizeForHumans()` | `string\|null` | Human-readable size (e.g. `"4.20 MiB"`)                    |
| `getMimeType()`          | `string\|null` | MIME type (e.g. `"audio/mpeg"`, `"video/mp4"`)             |
| `getMd5Data()`           | `string\|null` | MD5 hash of the data stream (when available)               |
| `getSha1Data()`          | `string\|null` | SHA-1 hash of the data stream (when available)             |

### Media-type detection

| Method       | Return type | Description                                            |
|--------------|-------------|--------------------------------------------------------|
| `isAudio()`  | `bool`      | `true` when the file has audio and **no** video stream |
| `isVideo()`  | `bool`      | `true` when the file contains a video stream           |
| `hasAudio()` | `bool`      | `true` when an audio stream is present                 |
| `hasVideo()` | `bool`      | `true` when a video stream is present                  |

### Duration

| Method                 | Return type    | Description                         |
|------------------------|----------------|-------------------------------------|
| `getPlaytime()`        | `string\|null` | Formatted duration (e.g. `"3:45"`)  |
| `getPlaytimeSeconds()` | `float`        | Duration in seconds (e.g. `225.48`) |

### Tags / metadata

| Method               | Return type    | Description                                |
|----------------------|----------------|--------------------------------------------|
| `getTitle()`         | `string`       | Track title; falls back to filename        |
| `getArtist()`        | `string\|null` | Artist name                                |
| `getAlbum()`         | `string\|null` | Album name                                 |
| `getComposer()`      | `string\|null` | Composer                                   |
| `getYear()`          | `string\|null` | Release year                               |
| `getGenres()`        | `array`        | List of genres                             |
| `getTrackNumber()`   | `string\|null` | Track number (e.g. `"4/12"`)               |
| `getDiscNumber()`    | `string\|null` | Disc/set number                            |
| `getCopyrightInfo()` | `string\|null` | Copyright string                           |
| `getComment()`       | `string\|null` | General comment or description tag         |
| `getLyrics()`        | `string\|null` | Embedded lyrics (unsynchronised lyric tag) |
| `getBpm()`           | `string\|null` | Beats per minute                           |

### Artwork

| Method                 | Return type          | Description                                                      |
|------------------------|----------------------|------------------------------------------------------------------|
| `getArtwork()`         | `string\|null`       | Embedded artwork as a base64-encoded string                      |
| `getArtwork(true)`     | `UploadedFile\|null` | Artwork saved to a temp file, returned as an `UploadedFile` JPEG |
| `getArtworkData()`     | `string\|null`       | Raw binary artwork data (no base64 overhead)                     |
| `getArtworkMimeType()` | `string\|null`       | MIME type of the artwork (e.g. `"image/jpeg"`)                   |

```php
// Base64 string — embed directly in an <img> src
$base64 = $track->getArtwork();

// UploadedFile JPEG — move it anywhere Laravel's Storage accepts
$jpeg = $track->getArtwork(true);
Storage::disk('public')->put('covers/'.$jpeg->getFilename(), file_get_contents($jpeg->getPathname()));

// Raw binary — pass straight to an image library
$binary = $track->getArtworkData();
$mime   = $track->getArtworkMimeType(); // "image/png"
```

### Audio stream

| Method                | Return type    | Description                                               |
|-----------------------|----------------|-----------------------------------------------------------|
| `getAudioCodec()`     | `string\|null` | Codec name (e.g. `"mp3"`, `"aac"`, `"flac"`, `"vorbis"`)  |
| `getSampleRate()`     | `int\|null`    | Sample rate in Hz (e.g. `44100`, `48000`)                 |
| `getBitrate()`        | `int\|null`    | Overall file bitrate in bps                               |
| `getAudioBitrate()`   | `int\|null`    | Audio track bitrate in bps                                |
| `getBitrateMode()`    | `string\|null` | `"cbr"`, `"vbr"`, or `"abr"`                              |
| `getChannels()`       | `int\|null`    | Channel count (1 = mono, 2 = stereo, 6 = 5.1, …)          |
| `getChannelMode()`    | `string\|null` | Channel layout string (e.g. `"stereo"`, `"joint stereo"`) |
| `getBitsPerSample()`  | `int\|null`    | Bit depth (e.g. `16`, `24`, `32`)                         |
| `isLossless()`        | `bool\|null`   | `true` for FLAC, ALAC, WAV, AIFF, etc.                    |
| `getEncoderOptions()` | `string\|null` | Encoder options string (e.g. LAME preset)                 |

```php
$track = GetId3::fromDiskAndPath('local', 'music/track.flac');

echo $track->getAudioCodec();    // "flac"
echo $track->getSampleRate();    // 96000
echo $track->getBitsPerSample(); // 24
echo $track->isLossless()
    ? 'Lossless'
    : 'Lossy';                   // "Lossless"
```

### Video stream

| Method                  | Return type    | Description                                                             |
|-------------------------|----------------|-------------------------------------------------------------------------|
| `getVideoCodec()`       | `string\|null` | Codec name (e.g. `"h264"`, `"hevc"`, `"vp9"`, `"av1"`)                  |
| `getVideoWidth()`       | `int\|null`    | Frame width in pixels                                                   |
| `getVideoHeight()`      | `int\|null`    | Frame height in pixels                                                  |
| `getVideoDimensions()`  | `array`        | `['width' => int, 'height' => int]`                                     |
| `getFrameRate()`        | `float\|null`  | Frames per second (e.g. `29.97`, `60.0`)                                |
| `getVideoBitrate()`     | `int\|null`    | Video track bitrate in bps                                              |
| `getVideoAspectRatio()` | `string\|null` | Simplified aspect ratio (e.g. `"16:9"`, `"4:3"`)                        |
| `getVideoRotation()`    | `int\|null`    | Clockwise rotation in degrees (0, 90, 180, 270) — set by mobile cameras |

```php
$video = GetId3::fromUploadedFile(request()->file('video'));

if ($video->isVideo()) {
    ['width' => $w, 'height' => $h] = $video->getVideoDimensions();
    echo "{$w}x{$h} @ {$video->getFrameRate()} fps"; // "1920x1080 @ 29.97 fps"
    echo $video->getVideoAspectRatio();               // "16:9"
    echo $video->getVideoCodec();                     // "h264"

    // Handle portrait video from a phone
    if ($video->getVideoRotation() === 90) {
        // swap width/height for display
    }
}
```

> **Container support for video metadata:** Dimensions, frame rate, codec, and rotation are resolved across MP4/QuickTime, Matroska (MKV/WebM), and RIFF/AVI containers automatically.

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todo list.

## Security

If you discover any security-related issues, please email owen.j@terktrendz.com instead of using the issue tracker.

## Credits

- [Owen Jubilant][link-author]
- [All Contributors][link-contributors]

## License

MIT. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/owen-oj/laravel-getid3.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/owen-oj/laravel-getid3.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-downloads]: https://packagist.org/packages/owen-oj/laravel-getid3
[link-author]: https://github.com/owen-oj
[link-contributors]: ../../contributors
