<?php

namespace TechnicPack\SolderClient\Resources;

class Build
{
    public $id;
    public $minecraft;
    public $java;
    public $memory;
    public $forge;
    public $mods = [];

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            if ($key !== "mods") {
                $this->{$key} = $properties[$key] ?? null;
            }
        }

        if (isset($properties['mods'])) {
            foreach ($properties['mods'] as $mod) {
                $this->mods[] = new Mod($mod);
            }

            usort($this->mods, function ($a, $b) {
                return strcasecmp($a->pretty_name, $b->pretty_name);
            });
        }
    }
}
