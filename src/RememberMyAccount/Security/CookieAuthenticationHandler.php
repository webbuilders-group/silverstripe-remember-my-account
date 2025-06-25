<?php
namespace WebbuildersGroup\RememberMyAccount\Security;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Security\MemberAuthenticator\CookieAuthenticationHandler as SS_CookieAuthenticationHandler;

class CookieAuthenticationHandler extends SS_CookieAuthenticationHandler
{
    use Configurable;

    private static $session_key_name = 'RememberMyAccount.member_id';

    /**
    * @param HTTPRequest $request
    * @return Member
    */
    public function authenticateRequest(HTTPRequest $request)
    {
        $member = parent::authenticateRequest($request);

        if ($member) {
            $request->getSession()->set($this->config()->session_key_name, $member->ID);
        }
    }

    /**
     * @param HTTPRequest $request
     */
    public function logOut(?HTTPRequest $request = null)
    {
        if ($request) {
            $request->getSession()->clear($this->config()->session_key_name);
        }

        return parent::logOut($request);
    }
}
