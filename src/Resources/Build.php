<?php

namespace TechnicPack\SolderClient\Resources;

class Build
{
    public $id;
    public $minecraft;
    public $minecraft_md5;
    public $java;
    public $memory;
    public $forge;
    public $mods = [];

    public function __construct($properties)
    {
        foreach ($properties as $key => $val) {
            if ($key != "mods") {
                $this->{$key} = $val;
            }
        }

        foreach ($properties['mods'] as $mod) {
            array_push($this->mods, new Mod($mod));
        }

        usort($this->mods, function ($a, $b) {
            return strcasecmp($a->pretty_name, $b->pretty_name);
        });
    }
}
