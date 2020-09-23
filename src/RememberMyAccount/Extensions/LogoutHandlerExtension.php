<?php
namespace WebbuildersGroup\RememberMyAccount\Extensions;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use WebbuildersGroup\RememberMyAccount\Security\CookieAuthenticationHandler;

class LogoutHandlerExtension extends Extension
{
    public function afterLogout()
    {
        $request = (Controller::has_curr() ? Controller::curr()->getRequest() : null);
        if ($request) {
            $member = Member::get()->byID(intval($request->getSession()->get(CookieAuthenticationHandler::config()->session_key_name)));
            if ($member instanceof Member) {
                Injector::inst()->get(IdentityStore::class)->logOut($request);
            }
        }
    }
}
