<?php

namespace TechnicPack\SolderClient\Resources;

class Mod
{
    public $id;
    public $name;
    public $version;
    public $md5;
    public $filesize;
    public $url;
    public $pretty_name;
    public $author;
    public $description;
    public $link;

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            $this->{$key} = $properties[$key] ?? null;
        }
    }
}