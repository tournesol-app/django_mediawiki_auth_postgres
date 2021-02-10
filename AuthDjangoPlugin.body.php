<?php
    /* AuthDjango.php. Based on https://github.com/mpetroff/django-mediawiki-authentication/blob/master/AuthDjango.body.php */

    require 'postgres_django_auth.php';


    function login_with_mw_params($username, $password) {
	// get parameters from the mediawiki config
	return call_user_func_array($username, $password, $db_password=$GLOBALS['wgAuthDjangoConfig']['DjangoDbPassword'],
		$host=$GLOBALS['wgAuthDjangoConfig']['DjangoDbHost'],
	        $port=$GLOBALS['wgAuthDjangoConfig']['DjangoDbPort'],
	        $dbname=$GLOBALS['wgAuthDjangoConfig']['DjangoDbName'],
	        $db_username=$GLOBALS['wgAuthDjangoConfig']['DjangoDbUsername'],
	        $auth_table=$GLOBALS=['wgAuthDjangoConfig']['DjangoAuthTable']);
    }
    
    class AuthDjango extends AuthPlugin {
        public function __construct() {
            // Disable mediawiki account creation
            $GLOBALS['wgGroupPermissions']['*']['createaccount'] = false;
        }
        
        public function userExists($username) {
	    try {
                // logging in with a wrong password
		// should return False if the user exists, otherwise throws an exception
                $result = login_with_mw_params($username, "");
		return true;
	    } catch(Exception $e) {
                return false;
	    }
        }
        
        public function autoCreate() {
            return false;
        }

        public function allowPasswordChange() {
            return false;
        }

        public function setPassword( $user, $password ) {
            return false;
        }
        
        public function strict() {
            return true;
        }

        public function strictUserAuth( $username ) {
            return false;
        }
        
        /**
         * Login in to mediawiki from an existing django session.
         * User must be logged in to django for this to work.
         *
         * @param object $user
         * @param bool $result
         * @return bool
         */
        public function onUserLoadFromSession($user, &$result) {
            global $wgLanguageCode, $wgRequest, $wgOut;
            $lg = Language::factory($wgLanguageCode);
            if (isset($_REQUEST['title']) && strstr($_REQUEST['title'], SpecialPageFactory::getLocalNameFor('Userlogin'))) {
                // Redirect to our login page
                $returnto = $wgRequest->getVal('returnto');
                // Don't redirect straight back to the logout page
                $returnto = (strstr($returnto, SpecialPageFactory::getLocalNameFor('Userlogout'))) ? '' : $returnto;
                $wgOut->redirect($GLOBALS['wgAuthDjangoConfig']['LinkToSiteLogin'] . '?next=' . $GLOBALS['wgAuthDjangoConfig']['LinkToWiki'] . $returnto);
            } elseif (array_key_exists('sessionid', $_COOKIE)) {
                $django_session = $_COOKIE['sessionid'];

                // find if there is a user connected to this session
                $r1 = $this->dbd->selectRow(
                    $this->session_table,
                    'session_data',
                    'session_key = \'' . $django_session . '\''
                );
                if ($r1) {
                    $decoded = json_decode(explode(':', base64_decode($r1->session_data), 2)[1]);
                    if (property_exists($decoded, '_auth_user_id')) {
                        $user_id = $decoded->_auth_user_id;
                        $r1 = $this->dbd->selectRow(
                            $this->user_table,
                            array(
                                'username',
                                'email'
                            ),
                            'id=' . $user_id
                        );
                    } else {
                        $r1 = false;
                    }
                }

                if ($r1) {
                    // there is a Django session present
                    $dbr = wfGetDB(DB_SLAVE);
                    $mw_uid = $dbr->selectField(
                        $this->authdjango_table,
                        'mw_user_id',
                        array(
                            'd_user_id' => $user_id
                        )
                    );

                    $local_id = ($mw_uid) ? $mw_uid : 0;

                    if (!$mw_uid) {
                        // Django user does not exist in MW djangouser table
                        // create a new user if one does not exist, and update
                        // djangouser table if one does
                        $username = $r1->username;
                        $email = $r1->email;

                        // replace space with underscore
                        // (site login doesn't allow spaces in usernames)
                        $username = str_replace(' ', '_', $username);
                        $u = User::newFromName($username);
                        if ($u->getID() == 0) {
                            // FIXME: Is the AuthDjango::userExists call necessary here?
                            if (AuthDjango::autoCreate() && AuthDjango::userExists($username)) {
                                $u->setEmail($email);
                                $u->confirmEmail();
                                $u->addToDatabase();
                                $u->setToken();
                            }
                        }
                        // Either a new MW user hs been created or there was an existing
                        // user with the same (ignoring spaces) username.
                        // In any case, update authdjango table.
                        $local_id = $u->getID();
                        $dbw = wfGetDB(DB_MASTER);
                        $dbw->insert(
                            $this->authdjango_table,
                            array(
                                'd_user_id' => $user_id,
                                'mw_user_id' => $local_id
                            )
                        );
                    }
                    
                    $user->setID($local_id);
                    $user->loadFromId();
                    $result = true;
                    $user->setCookies();
                    wfSetupSession();
                } else {
                    // if we're not logged in on the site make sure we're logged out of the database.
                    setcookie('wikidb_session', '', time()-3600);
                    unset($_COOKIE['wikidb_session']);
                    if (session_id() != "") {
                        session_destroy();
                    }
                }
            } else {
                // if we're not logged in on the site make sure we're logged out of the database.
                setcookie('wikidb_session', '', time()-3600);
                unset($_COOKIE['wikidb_session']);
                if (session_id() != "") {
                    session_destroy();
                }
            }
            
            return true;
        }
}
