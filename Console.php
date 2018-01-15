<?php

namespace Address;

class Console extends \Core\Module
{
	public static function countriesPopulateFromCsv($command, $args, $options)
	{
		$required = array('name', 'numeric', 'alpha2', 'alpha3', 'region');
		foreach ($required as $rq)
		{
			if (!isset($options[$rq]))
			{
				throw new \Exception('mandatory option not given: ' . $rq);
			}
		}
		$columns = array(
			'name'    => $options['name'],
			'numeric' => $options['numeric'],
			'alpha2'  => $options['alpha2'],
			'alpha3'  => $options['alpha3'],
			'region'  => $options['region'],
		);
		Countries::fromCsv($args['csv_file'], $columns);
	}

	public static function postcodesPopulateFromCsv($command, $args, $options)
	{
		$required = array('country', 'postcode', 'city');
		foreach ($required as $rq)
		{
			if (!isset($options[$rq]))
			{
				throw new \Exception('mandatory option not given: ' . $rq);
			}
		}

		$country = Country::find($options['country']);
		if (!$country)
		{
			throw new \Exception('country code not found from countries database: ' . $options['country']);
		}

		$columns = array(
			'postcode' => $options['postcode'],
			'locality' => isset($options['locality']) ? $options['locality'] : $options['city'],
			'city'     => $options['city'],
		);
		if (isset($options['state']))
		{
			$columns['state'] = $options['state'];
		}

		Postcodes::fromCsv($country, $args['csv_file'], $columns);
	}

	public static function postcodesPopulateFI($command, $args, $options)
	{
		$ch = curl_init('http://www.posti.fi/webpcode/unzip/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		if (!$data)
		{
			\kernel::log(LOG_ERR, 'unable to fetch base file listing for finnish postcode data');
			return false;
		}
		$n = preg_match('@http://www.posti.fi/webpcode/unzip/PCF_[0-9]*?\.dat@', $data, $url);
		if ($n < 1)
		{
			\kernel::log(LOG_ERR, 'file not found from finnish postcode file listing');
			return false;
		}
		$url = $url[0];
		$ch  = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
		if (!$data)
		{
			\kernel::log(LOG_ERR, 'unable to retrieve finnish postcode data');
			return false;
		}
		$n = preg_match_all('@PONOT(?P<date>.{8})(?P<postcode>.{5})(?P<postcode_fi_name>.{30})(?P<postcode_sv_name>.{30})(?P<postcode_abbr_fi>.{12})(?P<postcode_abbr_sv>.{12})(?P<valid_from>.{8})(?P<type_code>.{1})(?P<ad_area_code>.{5})(?P<ad_area_fi>.{30})(?P<ad_area_sv>.{30})(?P<municipal_code>.{3})(?P<municipal_name_fi>.{20})(?P<municipal_name_sv>.{20})(?P<municipal_language_ratio_code>.{1})@', $data, $results, PREG_SET_ORDER);
		if ($n < 1)
		{
			\kernel::log(LOG_ERR, 'no finnish postcodes found from data retrieved');
			return false;
		}
		$n  = 0;
		$em = get_entity_manager();
		for ($i = 0; $i < count($results); $i++)
		{
			$postcode = trim($results[$i]['postcode']);
			$c        = Postcode::find('FI', $postcode);
			if (!$c)
			{
				$c = new Postcode('FI', $postcode);
				$n++;
			}
			$locality = trim(utf8_encode($results[$i]['postcode_fi_name']));
			$city     = trim(utf8_encode($results[$i]['municipal_name_fi']));
			$state    = trim(utf8_encode($results[$i]['ad_area_fi']));
			$c->setLocality($locality);
			$c->setCity($city);
			$c->setState($state);
			$em->persist($c);
		}
		$em->flush();
		\kernel::log(LOG_INFO, 'finnish postcodes received, total found: ' . count($results) . ', added new: ' . $n);
		return true;
	}

	static public function test()
	{
		$p = Postcode::find('FI', 82730);
		$c = $p->getCountry();
		echo $p->getCountryCode() . ' ' . $p->getPostcode() . ":\n";
		echo $p->getLocality() . "\n";
		echo $p->getCity() . "\n";
		echo $p->getState() . "\n";
		echo $c->getName() . ' (' . $c->getRegion() . ")\n";
	}
}