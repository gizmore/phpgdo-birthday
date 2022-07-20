<?php
namespace GDO\Birthday;

use GDO\Core\GDT_Method;
use GDO\Birthday\Method\VerifyAge;
use GDO\Core\WithError;
use GDO\Core\WithInstance;

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
 
//     public function renderCell() : string
//     {
//     	$result = $this->execute();
//     	$html = $result->renderCell();
//     	return $html;
//     }
    
}
