<?php
/* AuthDjango.php. Based on https://github.com/mpetroff/django-mediawiki-authentication/blob/master/AuthDjango.body.php */

use MediaWiki\Auth\AuthManager;

require_once('postgres_django_auth.php');

function login_with_mw_params($username, $password) {
	// get parameters from the mediawiki config
	return login_django_postgres($username, $password, $db_password=$GLOBALS['wgAuthDjangoConfig']['DjangoDbPassword'],
		$host=$GLOBALS['wgAuthDjangoConfig']['DjangoDbHost'],
		$port=$GLOBALS['wgAuthDjangoConfig']['DjangoDbPort'],
		$dbname=$GLOBALS['wgAuthDjangoConfig']['DjangoDbName'],
		$db_username=$GLOBALS['wgAuthDjangoConfig']['DjangoDbUsername'],
		$auth_table=$GLOBALS['wgAuthDjangoConfig']['DjangoAuthTable']);
}

class AuthDjangoPlugin extends PluggableAuth {
public function authenticate( &$id, &$username, &$realname, &$email, &$errorMessage ) {
	// obtaining username and password
	$authManager = AuthManager::singleton();
	$extraLoginFields = $authManager->getAuthenticationSessionData(
		    PluggableAuthLogin::EXTRALOGINFIELDS_SESSION_KEY
	    );

	// DEBUG

	// logging in with Tournesol
	try {
	    $result = login_with_mw_params($extraLoginFields['ts_username'], $extraLoginFields['ts_password']);
	    $ok = $result['authorized'];
	    $username = $extraLoginFields['ts_username'];
	    $id = $result['id'];
	} catch (Exception $e) {
	    $ok = false;
	}
	return $ok;
}

public function saveExtraAttributes( $id ) {
}

public function deauthenticate( User &$user ) {
}

public static function onBeforeInitialize( \Title &$title, $unused, \OutputPage $output, \User $user, \WebRequest $request, \MediaWiki $mediaWiki) {
	// setting configuration variables
	global $wgPluggableAuth_ExtraLoginFields, $wgPluggableAuth_ButtonLabelMessage, $wgPluggableAuth_ButtonLabel, $wgPluggableAuth_Class;
	$wgPluggableAuth_ButtonLabelMessage = wfMessage('ts_login_btn');
	$wgPluggableAuth_ExtraLoginFields = ['ts_username' => ['type' => 'string', 'label' => wfMessage("tournesol_username")],
		'ts_password' => ['type' => 'password', 'label' => wfMessage('tournesol_password'), 'sensitive' => true]];
	$wgPluggableAuth_Class = 'AuthDjangoPlugin';
}
}
?>
