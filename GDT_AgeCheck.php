<?php
namespace GDO\Birthday;

use GDO\Birthday\Method\VerifyAge;
use GDO\Core\GDT_Method;
use GDO\Core\WithError;
use GDO\Core\WithInstance;

final class GDT_AgeCheck extends GDT_Method
{

	use WithError;
	use WithInstance;

	public $minAge;

	public function __construct()
	{
		parent::__construct();
		$this->method(VerifyAge::make());
	}

	public function getDefaultName(): ?string
	{
		return 'agecheck';
	}

	public function renderHTML(): string
	{
//     	GDT_Page::instance()->topResponse()->addField($this);
		return $this->_m()->getForm()->renderHTML();
	}

	private function _m(): VerifyAge
	{
		return $this->method;
	}

	public function minAge($minAge)
	{
		$this->minAge = $minAge;
		return $this;
	}

	public function errorMinAge()
	{
		$this->error('err_age_verify', [$this->minAge]);
		return $this;
	}

}
