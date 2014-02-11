<?php

namespace TechnicPack\SolderClient\Modpack;

use TechnicPack\SolderClient\Mod\Mod;

class Build
{
	private $minecraft;
	private $minecraft_md5;
	private $mods = array();

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