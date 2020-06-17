<?php
namespace WebbuildersGroup\RememberMyAccount\Security;

use SilverStripe\Security\Security;
use SilverStripe\Security\MemberAuthenticator\LoginHandler as SS_LoginHandler;

class LoginHandler extends SS_LoginHandler
{
    private static $allowed_actions = [
        'login',
    ];
    
    /**
     * URL handler for the log-in screen
     * @return array
     */
    public function login()
    {
        $title = '';
        if (!Security::getCurrentUser() && $this->getRequest()->getSession()->get(CookieAuthenticationHandler::config()->session_key_name) == null) {
            $title = _t(Security::class . '.LOGIN', 'Log in');
        }
        
        return [
            'Title' => $title,
            'Form' => $this->loginForm(),
        ];
    }
}
