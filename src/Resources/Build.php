<?php

namespace TechnicPack\SolderClient\Resources;

class Build
{
    public int $id;
    public string $minecraft;
    public ?string $java;
    public ?int $memory;
    public ?string $forge;
    /**
     * @var Mod[] $mods
     */
    public array $mods = [];

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

            usort($this->mods, function (Mod $a, Mod $b) {
                return strcasecmp($a->pretty_name, $b->pretty_name);
            });
        }
    }
}
