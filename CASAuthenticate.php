<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once 'modules/Users/authentication/SugarAuthenticate/SugarAuthenticate.php';

global $sugar_config;
require_once $sugar_config['cas']['library'];

/********************************************************************
 * Module that allows Sugar to perform user authentication using
 *  CAS.
 *********************************************************************/

class CASAuthenticate extends SugarAuthenticate
{
    var $userAuthenticateClass = 'CASAuthenticateUser';
    var $authenticationDir = 'CASAuthenticate';

    function CASAuthenticate()
    {
      parent::SugarAuthenticate();
      require_once('modules/Users/authentication/'. $this->authenticationDir . '/'. $this->userAuthenticateClass . '.php');
        $this->userAuthenticate = new $this->userAuthenticateClass();
        $this->doCASAuth();
    }

    function doCASAuth()
    {
        global $sugar_config;
        @session_start();

        // Don't try to login if the user is logging out
        if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'Logout') {
            $this->logout();
        // If the user is already authenticated, do this.
        } elseif (isset($_SESSION['authenticated_user_id']) ) {
            $this->sessionAuthenticate();
            return;
        } else {
          // Try to log the user in via SSO
            if ($this->userAuthenticate->loadUserOnLogin() == true) {
              parent::postLoginAuthenticate();
              header('Location: ' . $sugar_config['site_url']);
            } else {

            die(); //I should redirect here.  I'm not sure on the syntax -- sorry.
            } //end nested else.
        } // end top else.
    } //end doCASAuth()


    function sessionAuthenticate()
    {
        global $module, $action, $allowedActions;
        $authenticated = false;
        $allowedActions = array ("Authenticate", "Login"); // these are actions where the user/server keys aren't compared
        if (isset ($_SESSION['authenticated_user_id'])) {
            $GLOBALS['log']->debug("We have an authenticated user id: ".$_SESSION["authenticated_user_id"]);
            $authenticated = $this->postSessionAuthenticate();
        } else if (isset ($action) && isset ($module) && $action == "Authenticate" && $module == "Users") {
            $GLOBALS['log']->debug("We are NOT authenticating user now.  CAS will redirect.");
        }
        return $authenticated;
    } //end sessionAuthenticate()


    function postSessionAuthenticate()
    {
        global $action, $allowedActions, $sugar_config;
        $_SESSION['userTime']['last'] = time();
        $user_unique_key = (isset ($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : '';
        $server_unique_key = (isset ($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : '';

        //CHECK IF USER IS CROSSING SITES
        if (($user_unique_key != $server_unique_key) && (!in_array($action, $allowedActions)) && (!isset ($_SESSION['login_error']))) {

            session_destroy();
            $postLoginNav = '';
            if (!empty ($record) && !empty ($action) && !empty ($module)) {
                $postLoginNav = "&login_module=".$module."&login_action=".$action."&login_record=".$record;
            }
            $GLOBALS['log']->debug('Destroying Session User has crossed Sites');
            sugar_cleanup(true);
            die();
        }
        if (!$this->userAuthenticate->loadUserOnSession($_SESSION['authenticated_user_id'])) {
            session_destroy();
            $GLOBALS['log']->debug('Current user session does not exist redirecting to login');
            sugar_cleanup(true);
            die();
        }
        $GLOBALS['log']->debug('Current user is: '.$GLOBALS['current_user']->user_name);
        return true;
    } //end postSessionAuthenticate()

    function logout()
    {
        global $sugar_config;
        phpCAS::client(CAS_VERSION_2_0, $sugar_config['cas']['hostname'], $sugar_config['cas']['port'], $sugar_config['cas']['uri'], $sugar_config['cas']['changeSessionID']);
        phpCAS::setNoCasServerValidation();
        $params = array();
        if (!empty($sugar_config['cas']['logout_return_url'])) {
            $params = array(
                'url' => $sugar_config['cas']['logout_return_url'],
            );
        }
        phpCAS::logout($params);
    }

} //end CASAuthenticate class
