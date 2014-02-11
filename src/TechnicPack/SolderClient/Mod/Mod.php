<?php

namespace TechnicPack\SolderClient\Mod;

class Mod
{
	public $name;
	public $version;
	public $md5;
	public $url;

	public function __construct($properties)
	{
		foreach ($properties as $key => $val) {
			$this->{$key} = $val;
		}
	}
}