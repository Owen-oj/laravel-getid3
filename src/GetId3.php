<?php

namespace Owenoj\LaravelGetId3;

use Illuminate\Http\UploadedFile;

class GetId3
{
    protected $file;

    public function __construct(UploadedFile $file)
    {
        $this->file = $file;
    }

    private function getId3()
    {
        return new \getID3();
    }

    /**
     * Extract all available info from file.
     *
     * @return array
     */
    public function extractInfo()
    {
        $comments = ['comments' => []];
        $getid3 = $this->getId3();

        $info = $getid3->analyze($this->file);

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
     * Get the title of the media file.
     *
     * @return string
     * @throws \getid3_exception
     */
    public function getTitle()
    {
        $comments = $this->extractInfo()['comments'];

        $title = isset($comments['title'][0]) ? $comments['title'][0] : $this->extractInfo()['filename'];
        $this->title = $title;

        return title_case($title);
    }

    /**
     * Get the playtime of the media file.
     * @return mixed|null
     */
    public function getPlaytime()
    {
        return isset($this->extractInfo()['playtime_string']) ? $this->extractInfo()['playtime_string'] : null;
    }

    /**
     * Get the artwork of the media file.
     * @param  bool  $convert_to_jpeg
     * @return mixed|string
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
     * Convert base64 image to jpeg.
     * @param $base64_string
     * @return mixed
     */
    private function base64_to_jpeg($base64_string)
    {
        $output_file = uniqid().time().str_random(4).'-artwork.jpeg';
        // open the output file for writing
        $decoded = base64_decode($base64_string);
        $file = file_put_contents(sys_get_temp_dir().'/'.$output_file, $decoded);

        return new UploadedFile(sys_get_temp_dir().'/'.$output_file, $output_file);
    }
}
