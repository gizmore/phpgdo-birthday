<?php
namespace GDO\Birthday;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Form\GDT_Form;
use GDO\Friends\GDT_ACL;
use GDO\Core\GDT_UInt;
use GDO\User\GDO_User;
use GDO\Date\Time;
use GDO\Session\GDO_Session;
use GDO\Core\Application;
use GDO\Register\GDO_UserActivation;

/**
 * Birthday module.
 * - Birthday alerts
 * - Age verification for methods and global.
 * 
 * @author gizmore
 * @version 6.11.0
 * @since 6.10.1
 */
final class Module_Birthday extends GDO_Module
{
    public function onLoadLanguage() : void { $this->loadLanguage('lang/birthday'); }
    
    public function getConfig() : array
    {
        return [
            GDT_Checkbox::make('birthday_alerts')->initial('1'),
            GDT_UInt::make('global_min_age')->bytes(1)->unsigned()->initial('0'),
            GDT_UInt::make('method_min_age')->bytes(1)->unsigned()->initial('21'),
        ];
    }
    
    public function cfgBirthdayAlerts() { return $this->getConfigVar('birthday_alerts'); }
    public function cfgGlobalMinAge() { return $this->getConfigVar('global_min_age'); }
    public function cfgMethodMinAge() { return $this->getConfigVar('method_min_age'); }
    
    public function getUserSettings()
    {
        return [
            GDT_Birthdate::make('birthday'),
            GDT_ACL::make('age_visible')->initial('acl_noone'),
            GDT_ACL::make('birthdate_visible')->initial('acl_noone'),
            GDT_Checkbox::make('announce_my_birthday'),
        ];
    }
    
    /**
     * @TODO implement: On init, display other people birthda[yte]s.
     */
    public function onInit() : void
    {
    }
    
    public function onIncludeScripts() : void
    {
        $this->addCSS('css/birthday.css');
    }
    
    ####################
    ### Agecheck API ###
    ####################
    public function agecheckDisplay($minAge)
    {
        return GDT_AgeCheck::instance()->minAge($minAge)->errorMinAge();
    }
    
    public function agecheckIsMethodExcepted()
    {
        $mome = Application::$INSTANCE->mome();
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
    
    public function agecheckGlobal($minAge)
    {
        $user = GDO_User::current();
        $age = $this->getUserAge($user);
        return $age >= $minAge;
    }
    
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
    
    public function getUserAge(GDO_User $user)
    {
        return Time::getAge($this->getUserBirthdate($user));
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
    
    public function hookUserActivated(GDO_User $user, GDO_UserActivation $activation=null)
    {
    	if ($activation)
    	{
	        $data = $activation->gdoValue('ua_data');
	        if ($data['birthday'])
	        {
	            $this->saveUserSetting($user, 'birthday', $data['birthday']);
	        }
    	}
    }
    
    private function getUserAgeSession(GDO_User $user)
    {
        if (class_exists('GDO\Session\GDO_Session', false))
        {
            return GDO_Session::get('birthdate');
        }
    }
    
    #############
    ### Hooks ###
    #############
    public function hookBeforeExecute()
    {
        $app = Application::instance();
        if ( (!$app->isInstall()) && (!$app->isCLI()) )
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
    
}
