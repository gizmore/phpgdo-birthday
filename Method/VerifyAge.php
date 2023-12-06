<?php
namespace GDO\Birthday\Method;

use GDO\Birthday\GDT_Birthdate;
use GDO\Birthday\Module_Birthday;
use GDO\Core\GDT;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Session\GDO_Session;
use GDO\User\GDO_User;

/**
 * Show age verify form.
 *
 * @version 6.10.4
 * @author gizmore
 */
final class VerifyAge extends MethodForm
{

	public int $age = 18;

	public function isUserRequired(): bool
	{
		return false;
	}

	protected function createForm(GDT_Form $form): void
	{
		$form->action(href('Birthday', 'VerifyAge'));
		$form->text('info_age_verify', [$this->age]);
		$form->addFields(
			GDT_Birthdate::make()->notNull(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}

	public function formValidated(GDT_Form $form): GDT
	{
		$birthdate = $form->getFormVar('birthday');
		$this->saveBirthday($birthdate);
		return $this->message('msg_birthdate_session_set', [
			Time::displayDate($birthdate, 'day'),
			Time::displayAge($birthdate),
		]);
	}

	private function saveBirthday(string $birthdate)
	{
		GDO_Session::set('birthday', $birthdate);
		$user = GDO_User::current();
		if ($user->isUser())
		{
			Module_Birthday::instance()->saveUserSetting($user, 'birthday', $birthdate);
		}
	}

	public function age(int $age): self
	{
		$this->age = $age;
		return $this;
	}

}
