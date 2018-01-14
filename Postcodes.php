<?php

namespace Address;

class Postcodes
{
	public static function fromCsv($country, $file, array $columns, $delimiter = ',', $enclosure = '"', $escape = '\\')
	{
		/* try to open csv */
		$f = fopen($file, 'r');
		if (!$f)
		{
			throw new \Exception('unable to open postcodes source csv file for reading: ' . $file);
		}
		/* read contents */
		$postcodes = array();
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
				$fields   = array('postcode', 'locality', 'city', 'state');
				$postcode = array();
				foreach ($fields as $field)
				{
					$column = isset($columns[$field]) ? $columns[$field] : null;
					$value  = null;
					if ($column)
					{
						$value = $data[$headers[$column]];
					}
					$postcode[$field] = $value;
				}
				$postcodes[] = $postcode;
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
		$country_code = $country->getAlpha2();
		$total        = 0;
		$new          = 0;
		$em           = \kernel::getInstance()->getEntityManager();
		foreach ($postcodes as $postcode)
		{
			$p = Postcode::find($country_code, $postcode['postcode']);
			if (!$p)
			{
				$p = new Postcode($country_code, $postcode['postcode']);
				$new++;
			}
			$p->setLocality($postcode['locality']);
			$p->setCity($postcode['city']);
			$p->setState($postcode['state']);
			$em->persist($p);
			$total++;
		}
		$em->flush();
		\kernel::log(LOG_INFO, 'postcodes populated for country: ' . $country->getName() . ', total found: ' . $total . ', added new: ' . $new);
	}

	/**
	 * Find postcodes by optional country code and partial postcode.
	 */
	public static function find($country_code = null, $postcode = null)
	{
		$qb = \kernel::getInstance()->getEntityManager()->createQueryBuilder();
		$qb->select('i')->from('Address\Postcode', 'i');
		if ($country_code)
		{
			$qb->where('i.postcode LIKE :code');
			$q = $postcode ? $country_code . $postcode : $country_code;
			$qb->setParameter('code', $q . '%');
		}
		return $qb->getQuery()->getResult();
	}
}