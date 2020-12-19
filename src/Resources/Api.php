<?php

namespace TechnicPack\SolderClient\Resources;

class Api
{
    public $api;
	public $version;
	public $steam;

	public function __construct($properties)
	{
		foreach ($properties as $key => $val) {
			$this->{$key} = $val;
		}
	}
}
