<?php
namespace WebbuildersGroup\RememberMyAccount\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLVarchar;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\View\Requirements;
use WebbuildersGroup\RememberMyAccount\Security\CookieAuthenticationHandler;

class LoginForm extends MemberLoginForm
{
    /**
     * Constructor
     * @param LoginHandler $loginHandler The parent controller, necessary to create the appropriate form action tag.
     * @param string $name The method on the controller that will return this  form object.
     * @param FieldList $fields All of the fields in the form - a {@link FieldList} of {@link FormField} objects.
     * @param FieldList $actions All of the action buttons in the  form - a {@link FieldList} of {@link FormAction} objects
     * @param bool $checkCurrentUser If set to TRUE, it will be checked if a the user is currently logged in, and if so, only a logout button will be rendered
     * @param string $authenticatorClassName Name of the authenticator class that this form uses.
     */
    public function __construct($loginHandler, $authenticator, $name, $fields = null, $actions = null, $checkCurrentUser = true)
    {
        $request = $this->getRequest();
        if ($request->getVar('BackURL')) {
            $backURL = $request->getVar('BackURL');
        } else {
            $backURL = $request->getSession()->get('BackURL');
        }
        
        if ($checkCurrentUser) {
            $member = Security::getCurrentUser();
            if (!$member && $loginHandler->getRequest()->getSession()->get(CookieAuthenticationHandler::config()->session_key_name) > 0) {
                $member = Member::get()->byID(intval($loginHandler->getRequest()->getSession()->get(CookieAuthenticationHandler::config()->session_key_name)));
                if ($member) {
                    $fields = new FieldList(
                        //Regardless of what the unique identifer field is (usually 'Email'), it will be held in the 'Email' value, below:
                        HeaderField::create(
                            'Welcome',
                            DBField::create_field(
                                DBHTMLVarchar::class,
                                _t(__CLASS__ . '.WELCOME_BACK', '_Welcome Back {name}', ['name' => $member->Name]) .
                                '<a href="' .
                                Controller::join_links(
                                    Security::logout_url(),
                                    '?SecurityID=' . SecurityToken::inst()->getValue() . (isset($backURL) ? '&BackURL=' . rawurlencode($backURL) : '')
                                ) .
                                '" class="notUser">' . _t(__CLASS__ . '.NOT_USER', '_Not {name}?', ['name' => $member->FirstName]) . '</a>'
                            )
                        )->addExtraClass('remembered-account')->setAllowHTML(true),
                        new PasswordField('Password', _t(Member::class . '.PASSWORD', 'Password')),
                        new HiddenField("AuthenticationMethod", null, $authenticator, $this),
                        new HiddenField('Email', 'Email', $member->Email, null, $this)
                    );
                    
                    if (Security::config()->autologin_enabled) {
                        $fields->push(new HiddenField('Remember', 'Remember', 1));
                    }
                    
                    if (isset($backURL)) {
                        $fields->push(HiddenField::create('BackURL', 'BackURL', $backURL));
                    }
                    
                    $actions = new FieldList(
                        FormAction::create('dologin', _t(Member::class . '.BUTTONLOGIN', 'Log in'))
                                    ->setUseButtonTag(true)
                                    ->addExtraClass('button btn-primary font-icon-login'),
                        new LiteralField('forgotPassword', '<p id="ForgotPassword"><a href="Security/lostpassword">' . _t(Member::class . '.BUTTONLOSTPASSWORD', '_Forgot Password?') . '</a></p>')
                    );
                    
                    
                    Requirements::css('webbuilders-group/silverstripe-remember-my-account: css/loginform.css');
                    
                    $this->extend('updateRememberedAccountForm', $member, $fields, $actions);
                    
                    $this->addExtraClass('remembered-login-form');
                }
            }
        }
        
        parent::__construct($loginHandler, $authenticator, $name, $fields, $actions, false);
    }
}
