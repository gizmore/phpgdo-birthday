<?php
namespace GDO\Birthday;

use GDO\Core\GDT_Method;
use GDO\Birthday\Method\VerifyAge;

final class GDT_AgeCheck extends GDT_Method
{
    public function defaultName()
    {
        return 'agecheck';
    }
    
    public function __construct()
    {
        parent::__construct();
        $this->method(VerifyAge::make());
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
    
}
