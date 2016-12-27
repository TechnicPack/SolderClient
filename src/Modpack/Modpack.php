<?php

namespace TechnicPack\Modpack;

use TechnicPack\SolderClient;

class Modpack
{
	public $name;
	public $display_name;
	public $url;
	public $icon;
	public $icon_md5;
	public $logo;
	public $logo_md5;
	public $background;
	public $background_md5;
	public $recommended;
	public $latest;
	public $builds = array();

	public function __construct($properties)
	{
		foreach ($properties as $key => $val) {
			$this->{$key} = $val;
		}
	}
}