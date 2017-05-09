<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Users/authentication/LDAPAuthenticate/LDAPConfigs/default.php');
require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php');
global $sugar_config;
require_once $sugar_config['cas']['library'];

/**
 * This is called when a user logs in
 *
 * @param STRING $name
 * @param STRING $password
 * @return boolean
 */
class CASAuthenticateUser extends SugarAuthenticateUser {

    /**
     * This is called when a user logs in
     *
     * @param STRING $name
     * @param STRING $password
     * @return boolean
     */
     function loadUserOnLogin() {
        $name = $this->authUser();
        if (empty($name)) {
            return false;
        }
        else {
            return true;
        }
     } //end loadUserOnlogin()

   /**
    * Attempt to authenticate the user via CAS SSO
    */
    function authUser() {
	      global $sugar_config;
        phpCAS::setDebug();
        phpCAS::client(CAS_VERSION_2_0, $sugar_config['cas']['hostname'], $sugar_config['cas']['port'], $sugar_config['cas']['uri'], $sugar_config['cas']['changeSessionID']);
        if ((!empty($sugar_config['cas']['proxies'])) && (is_array($sugar_config['cas']['proxies']))) {
          phpCAS::allowProxyChain(new CAS_ProxyChain($sugar_config['cas']['proxies']));
        }
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();
        $authenticated = phpCAS::isAuthenticated();
        if ($authenticated)
        {
            $user_name = phpCAS::getUser();

	    $dbresult = $GLOBALS['db']->query("SELECT id, status FROM users WHERE user_name='" . $user_name . "' AND deleted = 0");			
            // User already exists use this one
            if ($row = $GLOBALS['db']->fetchByAssoc($dbresult)) {
                if ($row['status'] != 'Inactive')
                    return $this->loadUserOnSession($row['id']);
                else
                    return '';
            }
       	    echo 'Not authorized user. You may need to ask an administrator to give you access to SugarCRM. You may try logging in again <a href="https://' . $sugar_config['cas']['hostname'] . ':443/cas/logout?service=http://' . $_SERVER['SERVER_NAME'] . '">here</a>';
	    die();
            return ""; // SSO authentication was successful
        }
        else // not authenticated in CAS.
        {
            return;
        }
    } //end authenticateSSO();

} //End CASAuthenticateUser class.

?>
