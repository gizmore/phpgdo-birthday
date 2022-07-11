<?php
namespace GDO\Birthday;

use GDO\User\GDO_User;
use GDO\Core\Application;

/**
 * Use and Implement agecheckAge.
 * @author gizmore
 * @version 6.10.5
 * @since 6.10.4
 */
trait WithAgeCheck
{
    protected function agecheckAge()
    {
        return Module_Birthday::instance()->cfgMethodMinAge();
    }
    
    public function beforeExecute() : void
    {
        return $this->agecheckBeforeExecute();
    }
    
    protected function agecheckBeforeExecute()
    {
        $app = Application::instance();
        if ( (!$app->isInstall()) && (!$app->isCLI()) )
        {
            if (!$this->agecheckTest())
            {
                $minAge = $this->agecheckAge();
                return $this->error('err_age_verify', [
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
    
}
