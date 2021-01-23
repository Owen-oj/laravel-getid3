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
            Storage::disk($disk)->getSize($path),
            Storage::disk($disk)->readStream($path)
        );
    }

    /**
     * Get an instance of the underlying get getID3 class.
     * @return \getID3
     * @throws \getid3_exception
     */
    private function getId3()
    {
        return new \getID3();
    }

    /**
     * Extract all available info from file.
     *
     * @return array|string
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
        isset($info['comments']) ? $info['comments'] : ($info + $comments);

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
     * Get all comments.
     * @return mixed
     * @throws \getid3_exception
     */
    private function comments()
    {
        return $this->extractInfo()['comments'];
    }

    /**
     * Get the title of the media file.
     *
     * @return string
     * @throws \getid3_exception
     */
    public function getTitle()
    {
        return isset($this->comments()['title'][0]) ? $this->comments()['title'][0] : $this->extractInfo()['filename'];
    }

    /**
     * Get Album name.
     * @return string
     * @throws \getid3_exception
     */
    public function getAlbum()
    {
        return isset($this->comments()['album'][0]) ? $this->comments()['album'][0] : null;
    }

    /**
     * Get the playtime of the media file.
     * @return string|null
     * @throws \getid3_exception
     */
    public function getPlaytime()
    {
        return isset($this->extractInfo()['playtime_string']) ? $this->extractInfo()['playtime_string'] : null;
    }

    /**
     * Get number of seconds of playtime.
     * @return float
     * @throws \getid3_exception
     */
    public function getPlaytimeSeconds()
    {
        return isset($this->extractInfo()['playtime_seconds']) ?
            round($this->extractInfo()['playtime_seconds'], 2) : 0;
    }

    /**
     * Get the artwork of the media file.
     * @param  bool  $convert_to_jpeg
     * @return mixed|string
     * @throws \getid3_exception
     */
    public function getArtwork(bool $convert_to_jpeg = false)
    {
        $image = isset($this->extractInfo()['comments']['picture'][0]['data'])
            ? base64_encode($this->extractInfo()['comments']['picture'][0]['data']) : null;
        if (! is_null($image) && $convert_to_jpeg) {
            $image = $this->base64_to_jpeg($image);
        }

        return $image;
    }

    /**
     * Get genres.
     * @return array
     * @throws \getid3_exception
     */
    public function getGenres()
    {
        return isset($this->comments()['genre']) ? $this->comments()['genre'] : [];
    }

    /**
     * Get artist.
     * @return string|null
     * @throws \getid3_exception
     */
    public function getArtist()
    {
        return isset($this->comments()['artist'][0]) ? $this->comments()['artist'][0] : null;
    }

    /**
     * Get Composer of track.
     * @return string|null
     * @throws \getid3_exception
     */
    public function getComposer()
    {
        return isset($this->comments()['composer'][0]) ? $this->comments()['composer'][0] : null;
    }

    /**
     * Get Track number on album.
     * @return string|null
     * @throws \getid3_exception
     */
    public function getTrackNumber()
    {
        return isset($this->comments()['track_number'][0]) ? $this->comments()['track_number'][0] : null;
    }

    /**
     * Get the copyright info of the track.
     * @return string|null
     * @throws \getid3_exception
     */
    public function getCopyrightInfo()
    {
        return isset($this->comments()['copyright'][0]) ? $this->comments()['copyright'][0] : null;
    }

    /**
     * @return mixed|null
     * @throws \getid3_exception
     */
    public function getFileFormat()
    {
        return isset($this->extractInfo()['fileformat']) ? $this->extractInfo()['fileformat'] : null;
    }

    /**
     * Convert base64 image to jpeg.
     * @param $base64_string
     * @return mixed
     */
    private function base64_to_jpeg($base64_string)
    {
        $output_file = uniqid().time().Str::random(6).'-artwork.jpeg';
        // open the output file for writing
        $decoded = base64_decode($base64_string);
        $file = file_put_contents(sys_get_temp_dir().'/'.$output_file, $decoded);

        return new UploadedFile(sys_get_temp_dir().'/'.$output_file, $output_file);
    }
}
