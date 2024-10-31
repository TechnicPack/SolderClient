<?php

namespace TechnicPack\SolderClient\Resources;

class Modpack
{
    public string $name = "";
    public string $display_name = "";
    public ?string $url = null;
    public ?string $recommended = null;
    public ?string $latest = null;
    /**
     * @var Build[] $builds
     */
    public array $builds = [];

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            if (array_key_exists($key, $properties)) {
                $this->{$key} = $properties[$key];
            }
        }
    }
}