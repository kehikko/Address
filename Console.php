<?php

namespace Address;

class Console extends \Core\Module
{
	public static function createFromCsv($command, $args, $options)
	{
		$columns = array(
			'name'    => $options['name'],
			'numeric' => $options['numeric'],
			'alpha2'  => $options['alpha2'],
			'alpha3'  => $options['alpha3'],
			'region'  => $options['region'],
		);
		Countries::createFromCsv($args['csv_file'], $columns);
	}

	static public function test()
	{
		$c = new Countries();
	}
}