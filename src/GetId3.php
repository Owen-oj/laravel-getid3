<?php

namespace Owenoj\LaravelGetId3;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GetId3
{
    protected $file;

    protected $filesize;

    protected $fp;

    private $_info;

    public function __construct($filename, $filesize = null, $fp = null)
    {
        $this->file = $filename;
        $this->filesize = $filesize;
        $this->fp = $fp;
    }

    public static function fromUploadedFile(UploadedFile $file)
    {
        return new static($file);
    }

    public static function fromDiskAndPath($disk, $path)
    {
        return new static(
            $path,
            Storage::disk($disk)->size($path),
            Storage::disk($disk)->readStream($path)
        );
    }

    /**
     * Get an instance of the underlying getID3 class.
     *
     * @return \getID3
     *
     * @throws \getid3_exception
     */
    private function getId3()
    {
        return new \getID3();
    }

    /**
     * Extract all available info from file.
     *
     * @return array
     *
     * @throws \getid3_exception
     */
    public function extractInfo()
    {
        if (! isset($this->_info)) {
            $this->_info = $this->analyze();
        }

        return $this->_info;
    }

    private function analyze()
    {
        $comments = ['comments' => []];

        $info = $this->getId3()->analyze($this->file, $this->filesize, '', $this->fp);

        //if comments doesn't exist, we will add it ourselves
        $info = isset($info['comments']) ? $info : array_merge($info, $comments);

        if (! isset($info['comments']) && ! isset($info['tags'])) {
            $info = isset($info['id3v2']['comments']) ? array_merge($info,
                ['tags' => ['id3v2' => $info['id3v2']['comments']]]) : $info;
        }

        if (! isset($info['id3v2']) && isset($info['id3v1'])) {
            $info = isset($info['id3v1']['comments']) ? array_merge($info,
                ['tags' => ['id3v1' => $info['id3v1']['comments']]]) : $info;
        }

        \getid3_lib::CopyTagsToComments($info);

        return $info;
    }

    /**
     * Get all comments/tags from the media file.
     *
     * @return array
     *
     * @throws \getid3_exception
     */
    private function comments()
    {
        return $this->extractInfo()['comments'];
    }

    /**
     * Get the audio sub-array from getID3 info.
     *
     * @return array
     *
     * @throws \getid3_exception
     */
    private function audioInfo()
    {
        return $this->extractInfo()['audio'] ?? [];
    }

    /**
     * Get the video sub-array from getID3 info.
     *
     * Returns data from the top-level 'video' key. For container-specific
     * data (QuickTime/MP4, Matroska, RIFF/AVI) use the raw extractInfo() array.
     *
     * @return array
     *
     * @throws \getid3_exception
     */
    private function videoInfo()
    {
        return $this->extractInfo()['video'] ?? [];
    }

    // -------------------------------------------------------------------------
    // Tag / metadata methods
    // -------------------------------------------------------------------------

    /**
     * Get the title of the media file.
     *
     * Falls back to the filename when no title tag is present.
     *
     * @return string
     *
     * @throws \getid3_exception
     */
    public function getTitle()
    {
        return isset($this->comments()['title'][0]) ? $this->comments()['title'][0] : $this->extractInfo()['filename'];
    }

    /**
     * Get the album name.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getAlbum()
    {
        return isset($this->comments()['album'][0]) ? $this->comments()['album'][0] : null;
    }

    /**
     * Get the artist name.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getArtist()
    {
        return isset($this->comments()['artist'][0]) ? $this->comments()['artist'][0] : null;
    }

    /**
     * Get the composer of the track.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getComposer()
    {
        return isset($this->comments()['composer'][0]) ? $this->comments()['composer'][0] : null;
    }

    /**
     * Get the track number on the album.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getTrackNumber()
    {
        return isset($this->comments()['track_number'][0]) ? $this->comments()['track_number'][0] : null;
    }

    /**
     * Get the disc (set) number.
     *
     * Reads the `part_of_a_set` or `disc_number` tag, depending on the tag format.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getDiscNumber()
    {
        $comments = $this->comments();

        return $comments['part_of_a_set'][0]
            ?? $comments['disc_number'][0]
            ?? $comments['discnumber'][0]
            ?? null;
    }

    /**
     * Get the release year of the track.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getYear()
    {
        return isset($this->comments()['year'][0]) ? $this->comments()['year'][0] : null;
    }

    /**
     * Get the genres associated with the track.
     *
     * @return array
     *
     * @throws \getid3_exception
     */
    public function getGenres()
    {
        return isset($this->comments()['genre']) ? $this->comments()['genre'] : [];
    }

    /**
     * Get the copyright information of the track.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getCopyrightInfo()
    {
        return isset($this->comments()['copyright'][0]) ? $this->comments()['copyright'][0] : null;
    }

    /**
     * Get the general comment or description embedded in the tags.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getComment()
    {
        $comments = $this->comments();

        return $comments['comment'][0]
            ?? $comments['description'][0]
            ?? null;
    }

    /**
     * Get the lyrics embedded in the file.
     *
     * Checks the standard unsynchronised lyric tag first, then falls back to
     * the `lyrics` comment field used by some encoders.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getLyrics()
    {
        $comments = $this->comments();

        return $comments['unsynchronised_lyric'][0]
            ?? $comments['unsynchronized_lyric'][0]
            ?? $comments['lyrics'][0]
            ?? null;
    }

    /**
     * Get the beats-per-minute (BPM) value of the track.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getBpm()
    {
        return isset($this->comments()['bpm'][0]) ? $this->comments()['bpm'][0] : null;
    }

    // -------------------------------------------------------------------------
    // Artwork methods
    // -------------------------------------------------------------------------

    /**
     * Get the embedded artwork as a base64-encoded string.
     *
     * Pass `true` to receive an {@see UploadedFile} instance pointing to a
     * temporary JPEG file instead of the raw base64 string.
     *
     * @param  bool  $convert_to_jpeg  When true returns an UploadedFile JPEG.
     * @return string|UploadedFile|null  Base64 string, UploadedFile, or null.
     *
     * @throws \getid3_exception
     */
    public function getArtwork($convert_to_jpeg = false)
    {
        $image = isset($this->extractInfo()['comments']['picture'][0]['data'])
            ? base64_encode($this->extractInfo()['comments']['picture'][0]['data']) : null;
        if (! is_null($image) && $convert_to_jpeg) {
            $image = $this->base64_to_jpeg($image);
        }

        return $image;
    }

    /**
     * Get the raw binary data of the embedded artwork.
     *
     * Useful when you want to write the image directly to disk or pass it to
     * an image processing library without the overhead of base64 encoding.
     *
     * @return string|null  Raw binary data or null if no artwork is present.
     *
     * @throws \getid3_exception
     */
    public function getArtworkData()
    {
        return $this->extractInfo()['comments']['picture'][0]['data'] ?? null;
    }

    /**
     * Get the MIME type of the embedded artwork (e.g. `image/jpeg`, `image/png`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getArtworkMimeType()
    {
        return $this->extractInfo()['comments']['picture'][0]['image_mime'] ?? null;
    }

    // -------------------------------------------------------------------------
    // Playtime / duration methods
    // -------------------------------------------------------------------------

    /**
     * Get the formatted playtime string (e.g. `"3:45"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getPlaytime()
    {
        return isset($this->extractInfo()['playtime_string']) ? $this->extractInfo()['playtime_string'] : null;
    }

    /**
     * Get the playtime in seconds as a float rounded to two decimal places.
     *
     * @return float  Duration in seconds, or 0 when unknown.
     *
     * @throws \getid3_exception
     */
    public function getPlaytimeSeconds()
    {
        return isset($this->extractInfo()['playtime_seconds']) ?
            round($this->extractInfo()['playtime_seconds'], 2) : 0;
    }

    // -------------------------------------------------------------------------
    // File-level methods
    // -------------------------------------------------------------------------

    /**
     * Get the container / wrapper format of the file (e.g. `"mp3"`, `"mp4"`, `"flac"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getFileFormat()
    {
        return isset($this->extractInfo()['fileformat']) ? $this->extractInfo()['fileformat'] : null;
    }

    /**
     * Get the file size in bytes.
     *
     * For files opened via {@see fromDiskAndPath()} this is the value returned
     * by the storage driver. For local files getID3 reports the size directly.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getFileSize()
    {
        return $this->extractInfo()['filesize'] ?? null;
    }

    /**
     * Get a human-readable file size string (e.g. `"4.20 MB"`).
     *
     * Uses binary prefixes (KiB = 1024 bytes).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getFileSizeForHumans()
    {
        $bytes = $this->getFileSize();

        if ($bytes === null) {
            return null;
        }

        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }

    /**
     * Get the MIME type of the file (e.g. `"audio/mpeg"`, `"video/mp4"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getMimeType()
    {
        return $this->extractInfo()['mime_type'] ?? null;
    }

    /**
     * Get the MD5 hash of the audio/video data stream.
     *
     * Note: getID3 must be configured to compute this hash; it is not available
     * for all file types. Returns `null` when not present in the analysis data.
     *
     * @return string|null  Hexadecimal MD5 string or null.
     *
     * @throws \getid3_exception
     */
    public function getMd5Data()
    {
        return $this->extractInfo()['md5_data'] ?? null;
    }

    /**
     * Get the SHA-1 hash of the audio/video data stream.
     *
     * Same caveats as {@see getMd5Data()}.
     *
     * @return string|null  Hexadecimal SHA-1 string or null.
     *
     * @throws \getid3_exception
     */
    public function getSha1Data()
    {
        return $this->extractInfo()['sha1_data'] ?? null;
    }

    // -------------------------------------------------------------------------
    // Media-type detection
    // -------------------------------------------------------------------------

    /**
     * Determine whether the file contains a video stream.
     *
     * @return bool
     *
     * @throws \getid3_exception
     */
    public function hasVideo()
    {
        return ! empty($this->videoInfo());
    }

    /**
     * Determine whether the file contains an audio stream.
     *
     * @return bool
     *
     * @throws \getid3_exception
     */
    public function hasAudio()
    {
        return ! empty($this->audioInfo());
    }

    /**
     * Determine whether the file is primarily a video file.
     *
     * A file is considered a video when it contains a video stream,
     * regardless of whether it also has an audio track.
     *
     * @return bool
     *
     * @throws \getid3_exception
     */
    public function isVideo()
    {
        return $this->hasVideo();
    }

    /**
     * Determine whether the file is an audio-only file (no video stream).
     *
     * @return bool
     *
     * @throws \getid3_exception
     */
    public function isAudio()
    {
        return $this->hasAudio() && ! $this->hasVideo();
    }

    // -------------------------------------------------------------------------
    // Audio stream methods
    // -------------------------------------------------------------------------

    /**
     * Get the audio codec name (e.g. `"mp3"`, `"aac"`, `"flac"`, `"vorbis"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getAudioCodec()
    {
        $audio = $this->audioInfo();

        return $audio['codec'] ?? $audio['dataformat'] ?? null;
    }

    /**
     * Get the audio sample rate in Hz (e.g. `44100`, `48000`).
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getSampleRate()
    {
        return isset($this->audioInfo()['sample_rate']) ? (int) $this->audioInfo()['sample_rate'] : null;
    }

    /**
     * Get the overall bitrate of the file in bits per second.
     *
     * This is the combined bitrate reported at the file level and may differ
     * from the individual audio/video track bitrates.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getBitrate()
    {
        return isset($this->extractInfo()['bitrate']) ? (int) round($this->extractInfo()['bitrate']) : null;
    }

    /**
     * Get the audio track bitrate in bits per second.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getAudioBitrate()
    {
        return isset($this->audioInfo()['bitrate']) ? (int) round($this->audioInfo()['bitrate']) : null;
    }

    /**
     * Get the bitrate mode of the audio stream (`"cbr"`, `"vbr"`, or `"abr"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getBitrateMode()
    {
        return $this->audioInfo()['bitrate_mode'] ?? null;
    }

    /**
     * Get the number of audio channels (e.g. `1` for mono, `2` for stereo, `6` for 5.1).
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getChannels()
    {
        return isset($this->audioInfo()['channels']) ? (int) $this->audioInfo()['channels'] : null;
    }

    /**
     * Get the channel mode string (e.g. `"stereo"`, `"joint stereo"`, `"mono"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getChannelMode()
    {
        return $this->audioInfo()['channelmode'] ?? null;
    }

    /**
     * Get the bits per sample of the audio stream (e.g. `16`, `24`, `32`).
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getBitsPerSample()
    {
        return isset($this->audioInfo()['bits_per_sample']) ? (int) $this->audioInfo()['bits_per_sample'] : null;
    }

    /**
     * Determine whether the audio stream uses a lossless codec.
     *
     * Returns `true` for FLAC, ALAC, WAV, AIFF, etc. Returns `null` when the
     * information is not available in the analysis data.
     *
     * @return bool|null
     *
     * @throws \getid3_exception
     */
    public function isLossless()
    {
        $audio = $this->audioInfo();

        return array_key_exists('lossless', $audio) ? (bool) $audio['lossless'] : null;
    }

    /**
     * Get the encoder options or settings string embedded by the encoder.
     *
     * Common for MP3 files encoded with LAME (e.g. `"--preset standard"`).
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getEncoderOptions()
    {
        return $this->audioInfo()['encoder_options'] ?? null;
    }

    // -------------------------------------------------------------------------
    // Video stream methods
    // -------------------------------------------------------------------------

    /**
     * Get the video codec name (e.g. `"h264"`, `"hevc"`, `"vp9"`, `"av1"`).
     *
     * Checks the top-level `video` array first, then inspects container-specific
     * sub-arrays (QuickTime/MP4, Matroska, RIFF/AVI) for a more precise value.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getVideoCodec()
    {
        $video = $this->videoInfo();

        if (! empty($video['codec'])) {
            return $video['codec'];
        }

        $info = $this->extractInfo();

        // QuickTime / MP4
        if (! empty($info['quicktime']['video']['codec'])) {
            return $info['quicktime']['video']['codec'];
        }

        // Matroska (MKV / WebM) — iterate tracks
        if (! empty($info['matroska']['tracks']['tracks'])) {
            foreach ($info['matroska']['tracks']['tracks'] as $track) {
                if (($track['TrackType'] ?? null) === 1) { // 1 = video
                    return $track['CodecID'] ?? null;
                }
            }
        }

        return $video['dataformat'] ?? null;
    }

    /**
     * Get the video frame width in pixels.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getVideoWidth()
    {
        return $this->getVideoDimensions()['width'] ?? null;
    }

    /**
     * Get the video frame height in pixels.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getVideoHeight()
    {
        return $this->getVideoDimensions()['height'] ?? null;
    }

    /**
     * Get the video frame dimensions as an associative array.
     *
     * Checks the top-level `video` key and, when absent, falls back to
     * container-specific keys (QuickTime, Matroska, RIFF).
     *
     * @return array{width: int, height: int}|array{}
     *
     * @throws \getid3_exception
     */
    public function getVideoDimensions()
    {
        $video = $this->videoInfo();

        if (! empty($video['resolution_x']) && ! empty($video['resolution_y'])) {
            return [
                'width'  => (int) $video['resolution_x'],
                'height' => (int) $video['resolution_y'],
            ];
        }

        $info = $this->extractInfo();

        // QuickTime / MP4
        if (! empty($info['quicktime']['video']['resolution_x'])) {
            return [
                'width'  => (int) $info['quicktime']['video']['resolution_x'],
                'height' => (int) $info['quicktime']['video']['resolution_y'],
            ];
        }

        // Matroska (MKV / WebM)
        if (! empty($info['matroska']['tracks']['tracks'])) {
            foreach ($info['matroska']['tracks']['tracks'] as $track) {
                if (($track['TrackType'] ?? null) === 1
                    && isset($track['video']['PixelWidth'], $track['video']['PixelHeight'])
                ) {
                    return [
                        'width'  => (int) $track['video']['PixelWidth'],
                        'height' => (int) $track['video']['PixelHeight'],
                    ];
                }
            }
        }

        // RIFF / AVI
        if (! empty($info['riff']['video'][0]['resolution_x'])) {
            return [
                'width'  => (int) $info['riff']['video'][0]['resolution_x'],
                'height' => (int) $info['riff']['video'][0]['resolution_y'],
            ];
        }

        return [];
    }

    /**
     * Get the video frame rate in frames per second.
     *
     * @return float|null
     *
     * @throws \getid3_exception
     */
    public function getFrameRate()
    {
        $video = $this->videoInfo();

        if (! empty($video['frame_rate'])) {
            return (float) $video['frame_rate'];
        }

        $info = $this->extractInfo();

        // QuickTime / MP4
        if (! empty($info['quicktime']['video']['frame_rate'])) {
            return (float) $info['quicktime']['video']['frame_rate'];
        }

        // Matroska — derive from DefaultDuration (nanoseconds per frame)
        if (! empty($info['matroska']['tracks']['tracks'])) {
            foreach ($info['matroska']['tracks']['tracks'] as $track) {
                if (($track['TrackType'] ?? null) === 1
                    && ! empty($track['DefaultDuration'])
                ) {
                    return round(1_000_000_000 / $track['DefaultDuration'], 3);
                }
            }
        }

        return null;
    }

    /**
     * Get the video bitrate in bits per second.
     *
     * @return int|null
     *
     * @throws \getid3_exception
     */
    public function getVideoBitrate()
    {
        return isset($this->videoInfo()['bitrate']) ? (int) round($this->videoInfo()['bitrate']) : null;
    }

    /**
     * Get the video rotation in degrees (0, 90, 180, or 270).
     *
     * Rotation metadata is embedded by mobile devices when a video is recorded
     * in portrait mode. Only available for QuickTime / MP4 containers.
     *
     * @return int|null  Degrees of clockwise rotation, or null when not set.
     *
     * @throws \getid3_exception
     */
    public function getVideoRotation()
    {
        $info = $this->extractInfo();

        // QuickTime / MP4
        if (isset($info['quicktime']['video']['rotation'])) {
            return (int) $info['quicktime']['video']['rotation'];
        }

        return null;
    }

    /**
     * Get the display aspect ratio of the video as a simplified string (e.g. `"16:9"`).
     *
     * Returns `null` when dimensions are unavailable.
     *
     * @return string|null
     *
     * @throws \getid3_exception
     */
    public function getVideoAspectRatio()
    {
        $dimensions = $this->getVideoDimensions();

        if (empty($dimensions['width']) || empty($dimensions['height'])) {
            return null;
        }

        $gcd = $this->gcd($dimensions['width'], $dimensions['height']);

        return ($dimensions['width'] / $gcd).':'.($dimensions['height'] / $gcd);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Convert a base64-encoded image string to a temporary JPEG UploadedFile.
     *
     * @param  string  $base64_string
     * @return UploadedFile
     */
    private function base64_to_jpeg($base64_string)
    {
        $output_file = uniqid().time().Str::random(6).'-artwork.jpeg';
        $decoded = base64_decode($base64_string);
        file_put_contents(sys_get_temp_dir().'/'.$output_file, $decoded);
        
        return new UploadedFile(sys_get_temp_dir().'/'.$output_file, $output_file);
    }

    /**
     * Compute the greatest common divisor of two integers (Euclidean algorithm).
     *
     * Used internally to reduce aspect ratios to their simplest form.
     *
     * @param  int  $a
     * @param  int  $b
     * @return int
     */
    private function gcd(int $a, int $b): int
    {
        return $b === 0 ? $a : $this->gcd($b, $a % $b);
    }
}
