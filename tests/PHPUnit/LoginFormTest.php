<?php
namespace WebbuildersGroup\RememberMyAccount\Tests\PHPUnit;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Security\Security;
use SilverStripe\Security\MemberAuthenticator\SessionAuthenticationHandler;

class LoginFormTest extends FunctionalTest
{
    protected $usesDatabase = true;
    
    private $userPassword = '123456Ab$';
    
    
    protected function setUp()
    {
        parent::setUp();
        
        
        //Create the user and make sure they have a password
        $member = $this->createMemberWithPermission('ADMIN');
        $member->Password = $this->userPassword;
        $member->write();
    }
    
    /**
     * Test remembering the users account name
     */
    public function testRememberAccount()
    {
        //Load the login form for population later
        $response = $this->get(Security::login_url());
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Submit the login form
        $response = $this->submitForm(
            'LoginForm_LoginForm',
            'action_doLogin',
            [
                'Email' => 'ADMIN@example.org',
                'Password' => '123456Ab$',
                'Remember' => 1,
                'BackURL' => '', //Set the back url to nothing to ensure we don't get lost and we stay on the login page
            ]
        );
        
        
        //Make sure we were logged in properly
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('logged in as ADMIN', $response->getBody());
        
        
        //Kill the session memory of being logged in
        $this->session()->clear(Injector::inst()->get(SessionAuthenticationHandler::class)->getSessionVariable());
        Security::setCurrentUser(null); //Reset Security's memory of being logged in
        
        
        //Ensure out account is remembered
        $response = $this->get(Security::login_url());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('logged in as ADMIN', $response->getBody());
        $this->assertContains('Welcome Back ADMIN User', $response->getBody());
        $this->assertContains('Password', $response->getBody());
        
        
        //Submit the login form
        $response = $this->submitForm(
            'LoginForm_LoginForm',
            'action_doLogin',
            [
                'Password' => '123456Ab$',
                'BackURL' => '', //Set the back url to nothing to ensure we don't get lost and we stay on the login page
            ]
        );
        
        
        //Make sure we were logged in properly
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('logged in as ADMIN', $response->getBody());
    }
    
    /**
     * Test what happens when the user is logged out
     */
    public function testLogout()
    {
        //Load the login form for population later
        $response = $this->get(Security::login_url());
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Submit the login form
        $response = $this->submitForm(
            'LoginForm_LoginForm',
            'action_doLogin',
            [
                'Email' => 'ADMIN@example.org',
                'Password' => '123456Ab$',
                'Remember' => 1,
                'BackURL' => '', //Set the back url to nothing to ensure we don't get lost and we stay on the login page
            ]
        );
        
        
        //Make sure we were logged in properly
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('logged in as ADMIN', $response->getBody());
        
        
        //Try logging out
        $this->logOut();
        
        
        //Check the login page
        $response = $this->get(Security::login_url());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertNotContains('logged in as ADMIN', $response->getBody());
        $this->assertNotContains('Welcome Back ADMIN User', $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
