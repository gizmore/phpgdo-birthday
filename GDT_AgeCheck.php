<?php
namespace GDO\Birthday;

use GDO\Core\GDT_Method;
use GDO\Birthday\Method\VerifyAge;
use GDO\Core\GDT;
use GDO\Core\WithError;
use GDO\Core\WithInstance;
use GDO\Core\Website;
use GDO\UI\GDT_Page;

final class GDT_AgeCheck extends GDT_Method
{
	use WithError;
	use WithInstance;
	
    public function getDefaultName() : ?string
    {
        return 'agecheck';
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->method(VerifyAge::make());
    }
    
    private function _m(): VerifyAge
    {
    	return $this->method;
    }
    
    public $minAge;
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
    
    public function renderHTML(): string
    {
//     	GDT_Page::instance()->topResponse()->addField($this);
    	return $this->_m()->getForm()->renderHTML();
    }
    
}
