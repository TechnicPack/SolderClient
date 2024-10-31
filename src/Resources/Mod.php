<?php

namespace TechnicPack\SolderClient\Resources;

class Mod
{
    public string $name = "";
    public ?string $version = null;
    public string $md5 = "";
    public ?int $filesize = -1;
    public string $url = "";
    public string $pretty_name = "";
    public ?string $author = null;
    public ?string $description = null;
    public ?string $link = null;

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            if (array_key_exists($key, $properties)) {
                $this->{$key} = $properties[$key];
            }
        }
    }
}