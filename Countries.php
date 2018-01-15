<?php

namespace Address;

class Countries
{
	public static function fromCsv($file, array $columns, $delimiter = ',', $enclosure = '"', $escape = '\\')
	{
		/* try to open csv */
		$f = fopen($file, 'r');
		if (!$f)
		{
			throw new \Exception('unable to open countries source csv file for reading: ' . $file);
		}
		/* read contents */
		$countries = array();
		$headers   = null;
		while (($data = fgetcsv($f, 0, $delimiter, $enclosure, $escape)) !== false)
		{
			$n = count($data);
			if ($n < count($columns))
			{
				continue;
			}
			if ($headers)
			{
				$fields  = array('name', 'numeric', 'alpha2', 'alpha3', 'region');
				$country = array();
				foreach ($fields as $field)
				{
					$column = isset($columns[$field]) ? $columns[$field] : null;
					$value  = null;
					if ($column)
					{
						$value = $data[$headers[$column]];
					}
					$country[$field] = $value;
				}
				$countries[] = $country;
			}
			else
			{
				$headers = array();
				for ($i = 0; $i < $n; $i++)
				{
					$headers[$data[$i]] = $i;
				}
				foreach ($columns as $field => $column)
				{
					if (!isset($headers[$column]))
					{
						throw new \Exception('missing header: ' . $column);
					}
				}
			}
		}

		/* write to database */
		$em = get_entity_manager();
		foreach ($countries as $country)
		{
			$c = Country::find($country['numeric']);
			if (!$c)
			{
				$c = new Country($country);
				$em->persist($c);
			}
		}
		$em->flush();
	}
}