<?php

namespace TechnicPack\Resources;

class Mod
{
	public $name;
	public $version;
	public $md5;
	public $pretty_name;
	public $author;
	public $description;
	public $link;

	public function __construct($properties)
	{
		foreach ($properties as $key => $val) {
			$this->{$key} = $val;
		}
	}
}