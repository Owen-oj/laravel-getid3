<?php

namespace Owenoj\LaravelGetId3;

class GetId3
{
    protected $getID3;

    /**
     * GetId3 constructor.
     */
    public function __construct()
    {

    }

    /**
     * Using GetId3()
     * @param $file
     * @return array
     * @throws \getid3_exception
     */
    public function extract($file)
    {
        $comments = ['comments' => []];
        $getid3 = new \getID3();

        $info = $getid3->analyze($file);


        //if comments doesn't exist, we will add it ourselves
        isset($info['comments']) ? $info['comments'] : ($info + $comments);

        if (!isset($info['comments']) && !isset($info['tags'])) {
            $info = isset($info['id3v2']['comments']) ? array_merge($info, ['tags' => ['id3v2' => $info['id3v2']['comments']]]) : $info;

        }

        if (!isset($info['id3v2']) && isset($info['id3v1'])) {
            $info = isset($info['id3v1']['comments']) ? array_merge($info, ['tags' => ['id3v1' => $info['id3v1']['comments']]]) : $info;

        }

        \getid3_lib::CopyTagsToComments($info);
        return $info;
    }
}
