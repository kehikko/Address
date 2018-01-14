<?php

class Countries
{
	private $source_file = __DIR__ . '/data/countries.csv';
	private $columns     = array(
		'country' => 'name',
		'numeric' => 'country-code',
		'alpha-2' => 'alpha-2',
		'alpha-3' => 'alpha-3',
		'region'  => 'sub-region',
	);

	public function __construct($countries_csv = null, $columns = array())
	{

	}
}