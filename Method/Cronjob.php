<?php
namespace GDO\Birthday\Method;

use GDO\Birthday\Module_Birthday;
use GDO\Cronjob\MethodCronjob;
use GDO\Date\Time;
use GDO\DB\Result;
use GDO\Mail\Mail;
use GDO\User\GDO_User;

/**
 * Send email announcements for todays birthdays.
 * Runs daily at 7'o clock.
 *
 * @version 7.0.1
 * @since 7.0.1
 * @author gizmore
 */
final class Cronjob extends MethodCronjob
{

	public function runAt(): string
	{
		return $this->runDailyAt(7);
	}

	public function run(): void
	{
		if ($birthdaykids = $this->getBirthdayKids())
		{
			$users = $this->getTargets();
			while ($user = $users->fetchObject())
			{
				$this->sendMail($user, $birthdaykids);
			}
		}
	}

	###############
	### Private ###
	###############
	/**
	 * @return GDO_User[]
	 */
	private function getBirthdayKids(): array
	{
		$today = Time::getDate(0, 'm-d');
		$users = GDO_User::withSetting('Birthday', 'birthday', "____-{$today}%", 'LIKE');
		return array_filter($users, function (GDO_User $user)
		{
			return !!$user->settingVar('Birthday', 'announce_my_birthday');
		});
	}

	private function getTargets(): Result
	{
		return GDO_User::withSettingResult('Birthday', 'announce_me_birthdays', '1');
	}

	/**
	 * @param GDO_User[] $birthdaykids
	 */
	private function sendMail(GDO_User $user, array $birthdaykids): void
	{
		$mail = Mail::botMail();
		$mail->setSubject(tusr($user, 'mailsubj_birthdays', [sitename()]));
		$line = '';
		foreach ($birthdaykids as $kid)
		{
			$age = Module_Birthday::instance()->getUserAge($kid);
			$age = ceil($age);
			$line .= tusr($user, 'mailline_birthdays', [$kid->renderUserName(), $age]);
		}
		$args = [
			$user->renderUserName(),
			sitename(),
			$line,
		];
		$mail->setBody(tusr($user, 'mailbody_birthdays', $args));
		$mail->sendToUser($user);
	}

}
