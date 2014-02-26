<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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
        $name=$this->authUser();
        if(empty($name)){
            return false;
        }
        else{
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
        phpCAS::setNoCasServerValidation();
        phpCAS::forceAuthentication();
        $authenticated = phpCAS::isAuthenticated();
        if ($authenticated)
        {
            $user_name = phpCAS::getUser();

            $dbresult = $GLOBALS['db']->query("SELECT id, status FROM users WHERE user_name='" . $user_name . "' AND deleted = 0");
            // User already exists use this one
            if($row = $GLOBALS['db']->fetchByAssoc($dbresult)){
                if($row['status'] != 'Inactive') {
                    if ($sugar_config['cas']['updateUser']) {
                      $this->updateUserInfos($row['id'],phpCAS::getAttributes());
                    }
                    return $this->loadUserOnSession($row['id']);
                }
                else {
                    return '';
                }
            }
            elseif ($sugar_config['cas']['createUser']) {
                return $this -> createUser($user_name,phpCAS::getAttributes());
            }
            else {
               echo 'Not authorized user. You may need to ask an administrator to give you access to SugarCRM. You may try logging in again <a href="https://' . $sugar_config['cas']['hostname'] . ':443/cas/logout?service=http://' . $_SERVER['SERVER_NAME'] . '">here</a>';
               die();
            }
            return ""; // SSO authentication was successful
        }
        else // not authenticated in CAS.
        {
            return;
        }
    } //end authenticateSSO();

   /**
    * Create new user from CAS SSO informations
    */
    function createUser($name,$attrs=array()){
            $user = new User();
            $user->user_name = $name;
            $user->employee_status = 'Active';
            $user->status = 'Active';
            $user->is_admin = 0;
            $user->external_auth_only = 1;
            $user->save();
            $this -> setDefaultInfos($user);
            $this -> updateUserInfos($user->id,$attrs,$user);
            return $user->id;
    }

   /**
    * Define default user informations as specified in configuration
    */
    function setDefaultInfos($user) {
        global $sugar_config;
        if (is_array($sugar_config['cas']['default_attrs'])) {
            foreach($sugar_config['cas']['default_attrs'] as $attr => $value) {
                if (property_exists($user,$attr))
                    $user->$attr=$value;
            }
        }
        $user->save();

        if (is_array($sugar_config['cas']['default_prefs'])) {
            foreach($sugar_config['cas']['default_prefs'] as $pref => $value) {
                $user->setPreference($pref,$value);
            }
        }
        $user->savePreferencesToDB();
    }

   /**
    * Define user informations from CAS SSO attributes
    */
    function updateUserInfos($id,$attrs=array(),$user=Null) {
        global $sugar_config;
        if (is_null($user)) {
          $user = new User();
          $user->retrieve($id);
        }

        if (is_array($attrs) && is_array($sugar_config['cas']['attrs_map'])) {
            foreach($sugar_config['cas']['attrs_map'] as $cas_attr=>$crm_attr){
                if (isset($attrs[$cas_attr])) {
                    if (property_exists($user,$crm_attr))
                        $user->$crm_attr = $attrs[$cas_attr];
                }
            }
        }
        $user->save();
    }

} //End CASAuthenticateUser class.

?>
