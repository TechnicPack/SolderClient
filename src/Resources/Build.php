<?php

namespace TechnicPack\SolderClient\Resources;

class Build
{
    public int $id = -1;
    public string $minecraft = "";
    public ?string $java = null;
    public ?int $memory = -1;
    public ?string $forge = null;
    /**
     * @var Mod[] $mods
     */
    public array $mods = [];

    public function __construct($properties)
    {
        foreach (get_object_vars($this) as $key => $val) {
            if ($key !== "mods" && array_key_exists($key, $properties)) {
                $this->{$key} = $properties[$key];
            }
        }

        if (isset($properties['mods'])) {
            foreach ($properties['mods'] as $mod) {
                $this->mods[] = new Mod($mod);
            }

            usort($this->mods, function (Mod $a, Mod $b) {
                return strcasecmp($a->pretty_name, $b->pretty_name);
            });
        }
    }
}
