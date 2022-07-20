<?php
namespace GDO\Birthday\Method;

use GDO\Form\GDT_Form;
use GDO\Form\MethodForm;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Submit;
use GDO\Birthday\GDT_Birthdate;
use GDO\Session\GDO_Session;
use GDO\Date\Time;

/**
 * Show age verify form.
 * @author gizmore
 * @version 6.10.4
 */
final class VerifyAge extends MethodForm
{
    public int $age = 18;
    public function age(int $age) : self
    {
        $this->age = $age;
        return $this;
    }
    
    public function createForm(GDT_Form $form) : void
    {
    	$form->action(href('Birthday', 'VerifyAge'));
        $form->text('info_age_verify', [$this->age]);
        $form->addFields(
            GDT_Birthdate::make()->notNull(),
            GDT_AntiCSRF::make(),
        );
        $form->actions()->addField(GDT_Submit::make());
    }
    
    public function formValidated(GDT_Form $form)
    {
        $birthdate = $form->getFormVar('birthdate');
        GDO_Session::set('birthdate', $birthdate);
        return $this->message('msg_birthdate_session_set', [
            Time::displayDate($birthdate, 'day'),
            Time::displayAge($birthdate),
        ]);
    }
    
}
