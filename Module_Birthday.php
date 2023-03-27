<?php
namespace GDO\Birthday;

use GDO\Core\Application;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_UInt;
use GDO\Core\Method;
use GDO\Date\GDT_Duration;
use GDO\Date\Time;
use GDO\Form\GDT_Form;
use GDO\Register\GDO_UserActivation;
use GDO\Session\GDO_Session;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_Page;
use GDO\User\GDO_User;
use GDO\User\GDT_ACL;
use GDO\User\GDT_ACLRelation;
use GDO\Util\Strings;

/**
 * Birthday module.
 * - Birthday alerts
 * - Age verification for methods and global.
 *
 * @version 7.0.1
 * @since 6.10.1
 * @author gizmore
 */
final class Module_Birthday extends GDO_Module
{

	public function getFriendencies(): array
	{
		return [
			'Session',
		];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/birthday');
	}

	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('birthday_alerts')->initial('1'),
			GDT_UInt::make('global_min_age')->bytes(1)
				->unsigned()
				->initial('0'),
			GDT_UInt::make('method_min_age')->bytes(1)
				->unsigned()
				->initial('21'),
		];
	}

	public function getUserSettings(): array
	{
		return [
			GDT_Birthdate::make('birthday'),
			GDT_ACLRelation::make('age_visible')->initial('acl_noone')->noacl(),
			GDT_Checkbox::make('announce_my_birthday')->initial('0'),
			GDT_Checkbox::make('announce_me_birthdays')->initial('1'),
		];
	}

	public function onIncludeScripts(): void
	{
		$this->addCSS('css/birthday.css');
	}

	public function cfgBirthdayAlerts()
	{
		return $this->getConfigVar('birthday_alerts');
	}

	public function cfgMethodMinAge()
	{
		return $this->getConfigVar('method_min_age');
	}

	public function hookOnRegister(GDT_Form $form, GDO_UserActivation $activation)
	{
		$user = GDO_User::current();
		if ($birthdate = $this->getUserBirthdate($user))
		{
			$data = $activation->gdoValue('ua_data');
			$data['birthday'] = $birthdate;
			$activation->setValue('ua_data', $data);
		}
	}

	# ###################
	# ## Agecheck API ###
	# ###################

	public function getUserBirthdate(GDO_User $user)
	{
		if (!($birthdate = $this->userSettingVar($user, 'birthday')))
		{
			if (!($birthdate = $this->getUserAgeSession($user)))
			{
				return null;
			}
		}
		return $birthdate;
	}

	private function getUserAgeSession(GDO_User $user)
	{
		if (class_exists('GDO\Session\GDO_Session', false))
		{
			return GDO_Session::get('birthday');
		}
	}

	public function hookUserActivated(GDO_User $user, GDO_UserActivation $activation = null)
	{
		if ($activation)
		{
			$data = $activation->gdoValue('ua_data');
			if (@$data['birthday'])
			{
				$this->saveUserSetting($user, 'birthday', $data['birthday']);
			}
		}
	}

	public function hookBeforeServeAsset(string $url)
	{
		$url = Strings::substrTo($url, '?', $url);
		if (preg_match('/\\.(?:jpg|jpeg)$/iD', $url))
		{
			$minAge = $this->cfgGlobalMinAge();
			if (!$this->agecheckGlobal($minAge))
			{
				$method = $this->agecheckDisplay($minAge);
				GDT_Page::instance()->topResponse()->addField(
					$method->method->executeWithInputs(['submit' => 1]));
			}
		}
	}

	public function cfgGlobalMinAge()
	{
		return $this->getConfigVar('global_min_age');
	}

	public function agecheckGlobal($minAge)
	{
		$user = GDO_User::current();
		$age = $this->getUserAge($user);
		return $age >= $minAge;
	}

	public function getUserAge(GDO_User $user)
	{
		return Time::getAge($this->getUserBirthdate($user));
	}

	public function agecheckDisplay($minAge)
	{
		Application::$RESPONSE_CODE = 403;
		global $me;
		$method = GDT_AgeCheck::instance()->minAge($minAge)->errorMinAge();
		$me = $method->method;
		return $me ? $method : $method; # warning removed
	}

	/**
	 * Before any method is executed, check for age restrictions.
	 */
	public function hookBeforeExecute()
	{
		$app = Application::instance();
		if ((!$app->isInstall()) && (!$app->isCLI()))
		{
			$user = GDO_User::current();
			if (!$user->isStaff())
			{
				if ($minAge = $this->cfgGlobalMinAge())
				{
					if (!$this->agecheckIsMethodExcepted())
					{
						if (!$this->agecheckGlobal($minAge))
						{
							return $this->agecheckDisplay($minAge);
						}
					}
				}
			}
		}
	}

	# ############
	# ## Hooks ###
	# ############

	public function agecheckIsMethodExcepted()
	{
		/**
		 * @var $me Method
		 */
		global $me;
		$mome = $me->getModuleName() . '::' . $me->getMethodName();
		$mome = strtolower($mome);
		$exceptions = [
			'birthday::verifyage',
			'captcha::image',
			'language::gettransdata',
			'login::form',
			'dsgvo::accept',
			'core::fileserver',
		];
		return in_array($mome, $exceptions, true);
	}

	/**
	 * When we render the profile card, display age if ok.
	 */
	public function hookCreateCardUserProfile(GDT_Card $card)
	{
		/** @var $user GDO_User * */
		/** @var $acl GDT_ACL * */
		$profile = $card->gdo;
		$user = $profile->getUser();
		$acl = $user->setting('Birthday', 'age_visible');
		$reason = '';
		if ($acl->hasAccess(GDO_User::current(), $user, $reason))
		{
			$age = floor($this->getUserAge($user));
			$card->addField(GDT_Duration::make()->icon('birthday')
				->label('age')
				->initial("{$age}y"));
		}
	}

}
