<?php
namespace GDO\Birthday;

use GDO\Date\GDT_Date;

/**
 * A birthday datatype with default icon and label.
 * 
 * @author gizmore
 * @version 6.10.4
 * @since 6.10.1
 */
final class GDT_Birthdate extends GDT_Date
{
	public string $icon = 'birthday';
	public function getDefaultName() : ?string { return 'birthdate'; }
    public function defaultLabel() : self { return $this->label('birthdate'); }
	
}
