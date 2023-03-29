<?php

namespace TechnicPack\SolderClient\Resources;

class Modpack
{
    public $id;
    public $name;
    public $display_name;
    public $url;
    public $icon;
    public $logo;
    public $background;
    public $recommended;
    public $latest;
    public $builds = [];

    public function __construct($properties)
    {
        foreach ($properties as $key => $val) {
            $this->{$key} = $val;
        }
    }
}