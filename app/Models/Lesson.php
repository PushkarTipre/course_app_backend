<?php

namespace App\Models;

use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    use DefaultDatetimeFormat;


    protected $casts = ['video' => 'json'];

    public function setVideoAttribute($value)
    {
        $newVideo = [];
        foreach ($value as $k => $v) {
            $valueVideo = [];
            if (!empty($v["old_thumbnail"])) {
                $valueVideo["thumbnail"] = $v["old_thumbnail"];
            } else {
                $valueVideo["thumbnail"] = $v["thumbnail"];
            }
            if (!empty($v["old_url"])) {
                $valueVideo["url"] = $v["old_url"];
            } else {
                $valueVideo["url"] = $v["url"];
            }
            $valueVideo["name"] = $v["name"];
            array_push($newVideo, $valueVideo);
        }



        $this->attributes['video'] = json_encode(array_values($value));
    }


    public function getVideoAttribute($value)
    {
        $resvideo = json_decode($value, true) ?: [];

        if (!empty($resvideo)) {
            foreach ($resvideo as $key => $value) {
                // Check if 'url' key exists
                if (isset($value['url'])) {
                    $resvideo[$key]['url'] = env("APP_URL") . "uploads/" . $value['url'];
                } else {
                    $resvideo[$key]['url'] = ''; // or whatever default value you want
                }

                // Check if 'thumbnail' key exists
                if (isset($value['thumbnail'])) {
                    $resvideo[$key]['thumbnail'] = env("APP_URL") . "uploads/" . $value['thumbnail'];
                } else {
                    $resvideo[$key]['thumbnail'] = ''; // or whatever default value you want
                }
            }
        }
        return $resvideo;
    }
    public function getThumbnailAttribute($value)
    {
        return env("APP_URL") . "uploads/" . $value;
    }
}
