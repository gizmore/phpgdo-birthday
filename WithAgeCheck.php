<?php
namespace GDO\Birthday;

use GDO\Core\Application;
use GDO\User\GDO_User;

/**
 * Use and Implement agecheckAge.
 *
 * @version 6.10.5
 * @since 6.10.4
 * @author gizmore
 */
trait WithAgeCheck
{

	public function beforeExecute(): void
	{
		$this->agecheckBeforeExecute();
	}

	protected function agecheckBeforeExecute()
	{
		$app = Application::instance();
		if ((!$app->isInstall()) && (!$app->isCLI()))
		{
			if (!$this->agecheckTest())
			{
				$minAge = $this->agecheckAge();
				$this->error('err_age_verify', [
					$minAge,
				])->addField(GDT_AgeCheck::make()->minAge($minAge));
			}
		}
	}

	protected function agecheckTest()
	{
		$user = GDO_User::current();
		$module = Module_Birthday::instance();
		$age = $module->getUserAge($user);
		$minAge = $this->agecheckAge();
		return $age >= $minAge;
	}

	protected function agecheckAge()
	{
		return Module_Birthday::instance()->cfgMethodMinAge();
	}

}
