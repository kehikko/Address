<?php

namespace Address;

class Country
{
	private $country;
	private $numeric;
	private $alpha2;
	private $alpha3;
	private $region;

	public function __construct($code)
	{
		$numeric = intval($code) > 0 ? $code : null;
		$alpha2  = strlen($code) == 2 && !$numeric ? $code : null;
		$alpha3  = strlen($code) == 3 && !$numeric ? $code : null;
	}
}