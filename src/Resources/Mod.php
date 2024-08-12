<?php

namespace TechnicPack\SolderClient\Resources;

class Mod
{
    public int $id;
    public string $name;
    public string $version;
    public string $md5;
    public ?int $filesize;
    public string $url;
    public string $pretty_name;
    public ?string $author;
    public ?string $description;
    public ?string $link;

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            $this->{$key} = $properties[$key] ?? null;
        }
    }
}