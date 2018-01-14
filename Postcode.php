<?php

namespace Address;

class Postcode
{
	/**
	 * @var string
	 */
	private $postcode;

	/**
	 * @var string
	 */
	private $locality;

	/**
	 * @var string
	 */
	private $city;

	/**
	 * @var string
	 */
	private $state;

	public function __construct(string $country_code = '', string $postcode = '')
	{
		if (!empty($postcode))
		{
			if (strlen($postcode) < 3 || strlen($country_code) != 2)
			{
				throw new \Exception('invalid postcode/country code when creating new postcode entry, postcode: ' . $postcode . ', country code: ' . $country_code);
			}
			$this->postcode = $country_code . $postcode;
		}
	}

	public function getPostcode()
	{
		return substr($this->postcode, 2);
	}

	public function getCountryCode()
	{
		return substr($this->postcode, 0, 2);
	}

	/**
	 * Get country object for this postcode.
	 * @return \Address\Country Country object or null on errors
	 */
	public function getCountry()
	{
		return Country::find($this->getCountryCode());
	}

	public function getLocality()
	{
		return $this->locality;
	}

	public function setLocality($value)
	{
		$this->locality = $value;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setCity($value)
	{
		$this->city = $value;
	}

	public function getState()
	{
		return $this->state;
	}

	public function setState($value)
	{
		$this->state = $value;
	}

	/**
	 * Find postcode data by country code and postcode.
	 */
	public static function find(string $country_code, string $postcode)
	{
		$qb = \kernel::getInstance()->getEntityManager()->createQueryBuilder();
		$qb->select('i')
			->from('Address\Postcode', 'i')
			->where('i.postcode = :postcode')
			->setParameter('postcode', $country_code . $postcode);
		return $qb->getQuery()->getOneOrNullResult();
	}
}
