<?php

namespace Address;

class Country
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var integer
	 */
	private $numeric3;

	/**
	 * @var string
	 */
	private $alpha2;

	/**
	 * @var string
	 */
	private $alpha3;

	/**
	 * @var string
	 */
	private $region;

	public function __construct($data = null)
	{
		if (is_array($data))
		{
			$this->name     = $data['name'];
			$this->numeric3 = intval($data['numeric']);
			$this->alpha2   = $data['alpha2'];
			$this->alpha3   = $data['alpha3'];
			$this->region   = $data['region'];
		}
	}

	public function getName()
	{
		return $this->name;
	}

	public function getNumeric()
	{
		return $this->numeric3;
	}

	public function getAlpha2()
	{
		return $this->alpha2;
	}

	public function getAlpha3()
	{
		return $this->alpha3;
	}

	public function getRegion()
	{
		return $this->region;
	}

	/**
	 * Find by country code.
	 *
	 * @param  mixed $code           Code can be ISO 3166-1 numeric, alpha-2 or alpha-3
	 * @return mixed Address\Country object or null if not found
	 */
	public static function find($code)
	{
		// $numeric = intval($code) > 0 ? $code : null;
		// $alpha2  = strlen($code) == 2 && !$numeric ? $code : null;
		// $alpha3  = strlen($code) == 3 && !$numeric ? $code : null;

		$type = intval($code) > 0 ? 'numeric3' : null;
		$type = strlen($code) == 2 && !$type ? 'alpha2' : $type;
		$type = strlen($code) == 3 && !$type ? 'alpha3' : $type;
		if (!$type)
		{
			return null;
		}

		$qb = get_entity_manager()->createQueryBuilder();
		$qb->select('i')
			->from('Address\Country', 'i')
			->where('i.' . $type . ' = :code')
			->setParameter('code', $code);

		return $qb->getQuery()->getOneOrNullResult();
	}
}