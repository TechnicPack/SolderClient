<?php

namespace TechnicPack\SolderClient\Resources;

class Build
{
    public ?int $id = -1;
    public string $minecraft;
    public ?string $java = null;
    public ?int $memory = 0;
    public ?string $forge = null;
    /**
     * @var Mod[] $mods
     */
    public array $mods = [];

    public function __construct($properties)
    {
        if (array_key_exists('id', $properties) && is_numeric($properties['id'])) {
            $this->id = $properties['id'];
        }

        $this->minecraft = $properties['minecraft'];

        if (array_key_exists('java', $properties)) {
            $this->java = $properties['java'];
        }

        if (array_key_exists('memory', $properties) && is_numeric($properties['memory'])) {
            $this->memory = $properties['memory'];
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
