<?php

namespace TechnicPack\Resources;

class Build
{
	public $minecraft;
	public $minecraft_md5;
	public $mods = array();

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
	}
}